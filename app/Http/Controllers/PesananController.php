<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\TipeHarga;
use App\Models\Layanan;
use App\Models\MetodePembayaranTujuan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\RiwayatStatusPesanan;
use App\Models\Ulasan;
use App\Models\User;
use App\Services\OrderCalculationService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PesananController extends Controller
{
    public function __construct(
        protected OrderCalculationService $calculationService
    ) {}

    /**
     * Tampilkan formulir pemesanan joki (C-07).
     * Dapat dibuka dan diisi oleh siapa saja (tamu maupun customer).
     */
    public function create(Layanan $layanan): View|RedirectResponse
    {
        if (! $layanan->aktif || ! $layanan->game->aktif) {
            return redirect()->route('beranda')
                ->with('error', 'Layanan joki yang Anda pilih saat ini sedang tidak aktif.');
        }

        // Ambil item-item aktif layanan
        $itemsUtama = $layanan->items()
            ->where('jenis', 'utama')
            ->where('aktif', true)
            ->orderBy('urutan')
            ->get();

        $itemsTambahan = $layanan->items()
            ->where('jenis', 'tambahan')
            ->where('aktif', true)
            ->orderBy('urutan')
            ->get();

        // Kelompokkan wilayah & sub-wilayah jika tipe progres (misal eksplorasi map Genshin)
        $regions = collect();
        if ($layanan->tipe_harga === TipeHarga::Progres) {
            $parents = $itemsUtama->whereNull('parent_id');
            if ($parents->isNotEmpty()) {
                $regions = $parents->map(function ($parent) use ($itemsUtama) {
                    $parent->sub_wilayah = $itemsUtama->where('parent_id', $parent->id)->values();

                    return $parent;
                })->filter(fn ($p) => $p->sub_wilayah->isNotEmpty())->values();
            }
        }

        // Kategori item untuk tipe pilihan (misal: Archon Quest, World Quest, Story Quest)
        $categories = collect();
        if ($layanan->tipe_harga === TipeHarga::Pilihan) {
            $categories = $itemsUtama->pluck('kategori')->filter()->unique()->values();
        }

        // Cek jika ada draft yang tersimpan untuk layanan ini
        $draft = session('draft_pesanan');
        $draftLayananId = $draft['layanan_id'] ?? null;
        $draftData = ($draftLayananId === $layanan->id) ? $draft : null;

        return view('pesanan.create', compact('layanan', 'itemsUtama', 'itemsTambahan', 'regions', 'categories', 'draftData'));
    }

    /**
     * Simpan pesanan baru atau simpan draf ke sesi jika tamu belum login (C-07).
     */
    public function store(Request $request, Layanan $layanan): RedirectResponse
    {
        if (! $layanan->aktif || ! $layanan->game->aktif) {
            return back()->with('error', 'Layanan joki ini sedang tidak aktif.');
        }

        // Sanitasi nomor WhatsApp (buang spasi/strip dan normalkan +62/62 ke 08)
        if ($request->has('no_wa_customer')) {
            $rawWa = preg_replace('/[^0-9+]/', '', (string) $request->input('no_wa_customer'));
            if (str_starts_with($rawWa, '+62')) {
                $rawWa = '0'.substr($rawWa, 3);
            } elseif (str_starts_with($rawWa, '62')) {
                $rawWa = '0'.substr($rawWa, 2);
            }
            $request->merge(['no_wa_customer' => $rawWa]);
        }

        // Aturan validasi dasar
        $rules = [
            'id_akun_game' => ['required', 'string', 'max:100'],
            'no_wa_customer' => ['required', 'string', 'regex:/^08[1-9][0-9]{6,11}$/'],
            'zone_id' => ['nullable', 'string', 'max:50'],
            'server_game' => ['nullable', 'string', 'max:50'],
            'catatan_customer' => ['nullable', 'string', 'max:1000'],
        ];

        // Validasi bersyarat: akun login vs mabar/party (butuh_login_game)
        if ($layanan->butuh_login_game) {
            $rules['login_via'] = ['required', 'string', 'max:50'];
            $rules['username_akun_game'] = ['required', 'string', 'max:100'];
            $rules['password_akun_game'] = ['required', 'string', 'max:100'];
            $rules['nickname_game'] = ['nullable', 'string', 'max:100'];
            $rules['catatan_login'] = ['nullable', 'string', 'max:255'];
        } else {
            // Khusus layanan Gendong / Mabar / Party: customer main sendiri, hanya butuh nickname & WhatsApp
            $rules['nickname_game'] = ['required', 'string', 'max:100'];
            $rules['login_via'] = ['nullable', 'string', 'max:50'];
            $rules['username_akun_game'] = ['nullable', 'string', 'max:100'];
            $rules['password_akun_game'] = ['nullable', 'string', 'max:100'];
            $rules['catatan_login'] = ['nullable', 'string', 'max:255'];
        }

        // Validasi spesifik per tipe harga
        if ($layanan->tipe_harga->value === 'tangga') {
            $rules['tier_awal_id'] = ['required', 'exists:layanan_item,id'];
            $rules['bintang_awal'] = ['required', 'integer', 'min:0'];
            $rules['tier_tujuan_id'] = ['required', 'exists:layanan_item,id'];
            $rules['bintang_tujuan'] = ['required', 'integer', 'min:1'];
        } elseif ($layanan->tipe_harga->value === 'progres') {
            $rules['progres'] = ['required', 'array', 'min:1'];
            $rules['progres_selected'] = ['nullable', 'array'];
        } elseif ($layanan->tipe_harga->value === 'pilihan') {
            $rules['items'] = ['required', 'array', 'min:1'];
        }

        $validated = $request->validate($rules, [
            'id_akun_game.required' => 'ID Akun Game wajib diisi.',
            'no_wa_customer.required' => 'Nomor WhatsApp aktif wajib diisi untuk koordinasi joki.',
            'no_wa_customer.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format Indonesia yang benar (contoh: 08123456789 atau +628123456789).',
            'nickname_game.required' => 'Nickname Game wajib diisi agar Worker dapat mengundang Anda ke lobby party.',
            'login_via.required' => 'Pilih metode login akun game.',
            'username_akun_game.required' => 'Email atau Username login game wajib diisi.',
            'password_akun_game.required' => 'Password akun game wajib diisi.',
        ]);

        if (! $layanan->butuh_login_game) {
            $validated['login_via'] = 'Mabar / Gendong';
            $validated['username_akun_game'] = null;
            $validated['password_akun_game'] = null;
        }

        // Siapkan struktur payload data pesanan
        $payload = array_merge($validated, [
            'layanan_id' => $layanan->id,
            'item_tambahan_ids' => $request->input('item_tambahan_ids', []),
        ]);

        // Cek kalkulasi di server untuk memastikan pilihan valid sebelum disimpan
        try {
            $calcResult = $this->calculationService->hitung($layanan, $payload);
            if ($calcResult['total_harga'] <= 0) {
                return back()->withInput()->with('error', 'Total estimasi harga pesanan tidak valid.');
            }
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // JIKA PENGUNJUNG BELUM LOGIN: Simpan draf ke sesi
        if (! Auth::check()) {
            // Enkripsi password akun game sebelum disimpan ke session (defense-in-depth)
            if (! empty($payload['password_akun_game'])) {
                $payload['password_akun_game'] = Crypt::encryptString($payload['password_akun_game']);
                $payload['_password_encrypted'] = true;
            }

            session(['draft_pesanan' => $payload]);

            return redirect()->route('login')
                ->with('info', 'Draf pesanan Anda telah tersimpan! Silakan masuk atau daftar akun untuk melanjutkan pembayaran.');
        }

        // Pastikan hanya role customer yang membuat pesanan
        $user = Auth::user();
        if ($user->role !== Role::Customer) {
            return back()->withInput()->with('error', 'Hanya akun customer yang dapat membuat pesanan joki.');
        }

        // PROTEKSI ANTI-SPAM ATOMIC: Cek apakah masih ada pesanan menunggu_pembayaran yang aktif
        $pendingOrder = null;
        $pesanan = DB::transaction(function () use ($user, $layanan, $payload, $calcResult, &$pendingOrder) {
            $pendingOrder = Pesanan::query()
                ->where('customer_id', $user->id)
                ->where('status', StatusPesanan::MenungguPembayaran)
                ->where('batas_waktu_bayar', '>', now())
                ->lockForUpdate()
                ->first();

            if ($pendingOrder) {
                return null;
            }

            return $this->createOrderRecord($user->id, $layanan, $payload, $calcResult);
        });

        if (! $pesanan) {
            return redirect()->route('pesanan.show', $pendingOrder->kode_pesanan)
                ->with('error', "Anda masih memiliki pesanan {$pendingOrder->kode_pesanan} yang menunggu pembayaran. Silakan selesaikan pembayaran tersebut atau batalkan terlebih dahulu sebelum membuat pesanan baru.");
        }

        // Hapus draf sesi jika ada
        session()->forget('draft_pesanan');

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', "Pesanan {$pesanan->kode_pesanan} berhasil dibuat! Silakan lakukan pembayaran sebelum batas waktu.");
    }

    /**
     * Tampilkan halaman konfirmasi draf pesanan bagi pengguna yang baru login (C-07 langkah 5).
     */
    public function konfirmasiDraft(): View|RedirectResponse
    {
        $draft = session('draft_pesanan');
        if (! $draft || empty($draft['layanan_id'])) {
            return redirect()->route('beranda');
        }

        // Dekripsi password akun game jika sebelumnya dienkripsi di session
        if (! empty($draft['_password_encrypted']) && ! empty($draft['password_akun_game'])) {
            $draft['password_akun_game'] = Crypt::decryptString($draft['password_akun_game']);
            unset($draft['_password_encrypted']);
        }

        /** @var Layanan|null $layanan */
        $layanan = Layanan::with('game')->find($draft['layanan_id']);
        if (! $layanan) {
            session()->forget('draft_pesanan');

            return redirect()->route('beranda')->with('error', 'Layanan pada draf pesanan Anda tidak ditemukan.');
        }

        try {
            $calcResult = $this->calculationService->hitung($layanan, $draft);
        } catch (Exception $e) {
            session()->forget('draft_pesanan');

            return redirect()->route('game.show', $layanan->game)->with('error', $e->getMessage());
        }

        return view('pesanan.konfirmasi', compact('layanan', 'draft', 'calcResult'));
    }

    /**
     * Proses pembuatan pesanan dari konfirmasi draf.
     */
    public function prosesKonfirmasiDraft(): RedirectResponse
    {
        $draft = session('draft_pesanan');
        if (! $draft || empty($draft['layanan_id'])) {
            return redirect()->route('beranda');
        }

        // Dekripsi password akun game jika sebelumnya dienkripsi di session
        if (! empty($draft['_password_encrypted']) && ! empty($draft['password_akun_game'])) {
            $draft['password_akun_game'] = Crypt::decryptString($draft['password_akun_game']);
            unset($draft['_password_encrypted']);
        }

        $user = Auth::user();
        if ($user->role !== Role::Customer) {
            return redirect()->route('beranda')->with('error', 'Hanya akun customer yang dapat membuat pesanan.');
        }

        /** @var Layanan $layanan */
        $layanan = Layanan::findOrFail($draft['layanan_id']);

        try {
            $calcResult = $this->calculationService->hitung($layanan, $draft);
        } catch (Exception $e) {
            session()->forget('draft_pesanan');

            return redirect()->route('game.show', $layanan->game)->with('error', $e->getMessage());
        }

        // Cek proteksi anti-spam atomic
        $pendingOrder = null;
        $pesanan = DB::transaction(function () use ($user, $layanan, $draft, $calcResult, &$pendingOrder) {
            $pendingOrder = Pesanan::query()
                ->where('customer_id', $user->id)
                ->where('status', StatusPesanan::MenungguPembayaran)
                ->where('batas_waktu_bayar', '>', now())
                ->lockForUpdate()
                ->first();

            if ($pendingOrder) {
                return null;
            }

            return $this->createOrderRecord($user->id, $layanan, $draft, $calcResult);
        });

        if (! $pesanan) {
            session()->forget('draft_pesanan');

            return redirect()->route('pesanan.show', $pendingOrder->kode_pesanan)
                ->with('error', "Anda masih memiliki pesanan {$pendingOrder->kode_pesanan} yang menunggu pembayaran.");
        }

        session()->forget('draft_pesanan');

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', "Pesanan {$pesanan->kode_pesanan} berhasil dikonfirmasi! Silakan lakukan pembayaran transfer.");
    }

    /**
     * Batalkan dan buang draf pesanan dari sesi.
     */
    public function batalkanDraft(): RedirectResponse
    {
        session()->forget('draft_pesanan');

        return redirect()->route('beranda')->with('info', 'Draf pesanan telah dibatalkan.');
    }

    /**
     * Tampilkan detail & checkout tagihan pesanan (C-08).
     */
    public function show(Pesanan $pesanan): View
    {
        $this->authorizeView($pesanan);

        // Just-in-time check: jika waktu bayar lewat dan masih menunggu pembayaran, ubah ke kedaluwarsa
        if ($pesanan->status === StatusPesanan::MenungguPembayaran && $pesanan->sudahKedaluwarsa()) {
            $pesanan->update(['status' => StatusPesanan::Kedaluwarsa]);
            $pesanan->hapusPasswordAkunGame();

            RiwayatStatusPesanan::create([
                'pesanan_id' => $pesanan->id,
                'status_lama' => StatusPesanan::MenungguPembayaran->value,
                'status_baru' => StatusPesanan::Kedaluwarsa->value,
                'catatan' => 'Batas waktu pembayaran 2 jam telah habis. Pesanan kedaluwarsa dan kredensial password game telah dihapus.',
                'diubah_oleh' => null,
            ]);

            $pesanan->refresh();
        }

        $pesanan->load([
            'layanan.game',
            'pesananItems',
            'pembayaran',
            'riwayatStatus' => fn ($q) => $q->with('pengubah')->orderBy('created_at'),
            'customer',
            'worker',
            'laporanTerbaru',
            'ulasan',
        ]);

        // Rekening pembayaran aktif
        $metodePembayaran = MetodePembayaranTujuan::aktif()->get();

        // Sisa detik untuk countdown (hanya jika status masih menunggu_pembayaran)
        $sisaDetik = ($pesanan->status === StatusPesanan::MenungguPembayaran && $pesanan->batas_waktu_bayar)
            ? max(0, (int) now()->diffInSeconds($pesanan->batas_waktu_bayar, false))
            : 0;

        return view('pesanan.show', compact('pesanan', 'metodePembayaran', 'sisaDetik'));
    }

    /**
     * Batalkan pesanan mandiri oleh customer saat status menunggu_pembayaran.
     */
    public function batalkan(Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeOwner($pesanan);

        if ($pesanan->status !== StatusPesanan::MenungguPembayaran) {
            return back()->with('error', 'Hanya pesanan yang sedang menunggu pembayaran yang dapat dibatalkan.');
        }

        $pesanan->update([
            'status' => StatusPesanan::Dibatalkan,
        ]);

        // Hapus kredensial seketika
        $pesanan->hapusPasswordAkunGame();

        RiwayatStatusPesanan::create([
            'pesanan_id' => $pesanan->id,
            'status_lama' => StatusPesanan::MenungguPembayaran->value,
            'status_baru' => StatusPesanan::Dibatalkan->value,
            'catatan' => 'Pesanan dibatalkan sendiri oleh customer. Kredensial akun game telah dihapus total.',
            'diubah_oleh' => Auth::id(),
        ]);

        return back()->with('success', "Pesanan {$pesanan->kode_pesanan} berhasil dibatalkan. Kredensial game Anda telah dihapus dengan aman.");
    }

    /**
     * Unggah bukti transfer pembayaran manual oleh customer (C-09).
     */
    public function unggahBukti(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeOwner($pesanan);

        // Cek kedaluwarsa sebelum menerima unggahan bukti
        if ($pesanan->sudahKedaluwarsa() || $pesanan->status !== StatusPesanan::MenungguPembayaran) {
            if ($pesanan->sudahKedaluwarsa() && $pesanan->status === StatusPesanan::MenungguPembayaran) {
                $pesanan->update(['status' => StatusPesanan::Kedaluwarsa]);
                $pesanan->hapusPasswordAkunGame();
                RiwayatStatusPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'status_lama' => StatusPesanan::MenungguPembayaran->value,
                    'status_baru' => StatusPesanan::Kedaluwarsa->value,
                    'catatan' => 'Batas waktu pembayaran 2 jam telah habis saat mengunggah bukti pembayaran.',
                    'diubah_oleh' => null,
                ]);
            }

            return back()->with('error', 'Pesanan ini sudah tidak dalam status menunggu pembayaran atau batas waktu telah habis.');
        }

        $request->validate([
            'metode_pembayaran' => ['required', 'string', 'max:60'],
            'bukti_transfer' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'metode_pembayaran.required' => 'Pilihlah rekening bank / e-wallet tujuan transfer.',
            'bukti_transfer.required' => 'Berkas foto/struk bukti transfer wajib diunggah.',
            'bukti_transfer.image' => 'Berkas bukti transfer harus berupa gambar.',
            'bukti_transfer.mimes' => 'Format gambar yang didukung: JPG, JPEG, PNG, atau WebP.',
            'bukti_transfer.max' => 'Ukuran berkas bukti transfer maksimal 2 MB.',
        ]);

        // Simpan file ke storage privat (bukan disk public)
        $path = $request->file('bukti_transfer')->store('bukti_pembayaran', 'local');

        try {
            DB::transaction(function () use ($pesanan, $request, $path) {
                // Hapus berkas bukti transfer lama jika ada (mencegah orphaned files - Bug #27)
                $oldFile = $pesanan->pembayaran?->bukti_transfer;
                if ($oldFile && Storage::disk('local')->exists($oldFile)) {
                    Storage::disk('local')->delete($oldFile);
                }

                // Simpan rekaman pembayaran
                Pembayaran::updateOrCreate(
                    ['pesanan_id' => $pesanan->id],
                    [
                        'metode_pembayaran' => $request->metode_pembayaran,
                        'bukti_transfer' => $path,
                        'status' => StatusPembayaran::MenungguVerifikasi,
                    ]
                );

                // Ubah status pesanan menjadi menunggu_verifikasi
                $pesanan->update([
                    'status' => StatusPesanan::MenungguVerifikasi,
                ]);

                // Catat riwayat status
                RiwayatStatusPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'status_lama' => StatusPesanan::MenungguPembayaran->value,
                    'status_baru' => StatusPesanan::MenungguVerifikasi->value,
                    'catatan' => "Bukti transfer telah diunggah via {$request->metode_pembayaran}. Menunggu verifikasi oleh Admin.",
                    'diubah_oleh' => Auth::id(),
                ]);
            });
        } catch (\Throwable $e) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', 'Bukti pembayaran berhasil diunggah! Admin kami akan segera memverifikasi transaksi Anda.');
    }

    /**
     * Unduh / tampilkan berkas bukti transfer secara privat.
     */
    public function unduhBukti(Pesanan $pesanan): StreamedResponse
    {
        $this->authorizeView($pesanan);

        if (! $pesanan->pembayaran || ! $pesanan->pembayaran->bukti_transfer) {
            abort(404, 'Bukti transfer tidak ditemukan.');
        }

        $path = $pesanan->pembayaran->bukti_transfer;
        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'Berkas bukti transfer tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('local')->response($path);
    }

    /**
     * Tampilkan halaman riwayat & daftar pesanan milik customer (C-08).
     */
    public function pesananSaya(): View
    {
        $user = Auth::user();

        // Pesanan aktif (butuh tindakan atau sedang berjalan)
        $pesananAktif = Pesanan::with(['layanan.game'])
            ->where('customer_id', $user->id)
            ->whereNotIn('status', [
                StatusPesanan::Selesai,
                StatusPesanan::Dibatalkan,
                StatusPesanan::Kedaluwarsa,
            ])
            ->orderByDesc('id')
            ->get();

        // Riwayat pesanan (sudah selesai atau terminal)
        $pesananRiwayat = Pesanan::with(['layanan.game'])
            ->where('customer_id', $user->id)
            ->whereIn('status', [
                StatusPesanan::Selesai,
                StatusPesanan::Dibatalkan,
                StatusPesanan::Kedaluwarsa,
            ])
            ->orderByDesc('id')
            ->paginate(10);

        return view('customer.pesanan', compact('pesananAktif', 'pesananRiwayat'));
    }

    /**
     * Simpan perbaikan data akun game oleh customer (C-10).
     */
    public function simpanRevisiData(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeOwner($pesanan);

        if ($pesanan->status !== StatusPesanan::PerluRevisiData) {
            return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
                ->with('error', 'Pesanan ini tidak sedang membutuhkan revisi data akun.');
        }

        // Sanitasi nomor WhatsApp jika diisi (Bug #23)
        if ($request->has('no_wa_customer') && $request->filled('no_wa_customer')) {
            $rawWa = preg_replace('/[^0-9+]/', '', (string) $request->input('no_wa_customer'));
            if (str_starts_with($rawWa, '+62')) {
                $rawWa = '0'.substr($rawWa, 3);
            } elseif (str_starts_with($rawWa, '62')) {
                $rawWa = '0'.substr($rawWa, 2);
            }
            $request->merge(['no_wa_customer' => $rawWa]);
        }

        $validated = $request->validate([
            'id_akun_game'        => ['required', 'string', 'max:100'],
            'login_via'           => ['nullable', 'string', 'max:30'],
            'username_akun_game'  => ['nullable', 'string', 'max:100'],
            'password_akun_game'  => ['nullable', 'string', 'min:4', 'max:255'],
            'nickname_game'       => ['nullable', 'string', 'max:100'],
            'server_game'         => ['nullable', 'string', 'max:50'],
            'zone_id'             => ['nullable', 'string', 'max:50'],
            'no_wa_customer'      => ['nullable', 'string', 'regex:/^08[1-9][0-9]{6,11}$/'],
            'catatan_customer'    => ['nullable', 'string', 'max:1000'],
        ], [
            'id_akun_game.required' => 'ID Akun game wajib diisi.',
            'password_akun_game.min' => 'Password minimal 4 karakter.',
            'no_wa_customer.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format Indonesia yang benar (contoh: 08123456789 atau +628123456789).',
        ]);

        $dataTambahan = $pesanan->data_tambahan ?? [];
        if (isset($validated['zone_id'])) {
            $dataTambahan['zone_id'] = $validated['zone_id'];
        }

        $updatePayload = [
            'id_akun_game'       => $validated['id_akun_game'],
            'login_via'          => $validated['login_via'] ?? $pesanan->login_via,
            'username_akun_game' => $validated['username_akun_game'] ?? $pesanan->username_akun_game,
            'nickname_game'      => $validated['nickname_game'] ?? $pesanan->nickname_game,
            'server_game'        => $validated['server_game'] ?? $pesanan->server_game,
            'no_wa_customer'     => $validated['no_wa_customer'] ?? $pesanan->no_wa_customer,
            'catatan_customer'   => $validated['catatan_customer'] ?? $pesanan->catatan_customer,
            'data_tambahan'      => $dataTambahan,
            'status'             => StatusPesanan::MenungguVerifikasi,
            'data_disetujui'     => false,
        ];

        // Jika customer mengisi password baru, update terenkripsi
        if (! empty($validated['password_akun_game'])) {
            $updatePayload['password_akun_game'] = $validated['password_akun_game'];
        }

        DB::transaction(function () use ($pesanan, $updatePayload) {
            $pesanan->update($updatePayload);

            $pesanan->catatRiwayat(
                StatusPesanan::PerluRevisiData->value,
                StatusPesanan::MenungguVerifikasi->value,
                Auth::id(),
                'Customer telah memperbarui data akun game. Menunggu verifikasi ulang oleh Admin.'
            );
        });

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', 'Data akun game berhasil diperbarui! Admin akan segera memverifikasi pesanan Anda.');
    }

    /**
     * Setujui hasil pengerjaan joki oleh customer (C-11).
     * Status diubah menjadi hasil_disetujui dan password akun game dihapus seketika.
     */
    public function setujuiHasil(Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeOwner($pesanan);

        if ($pesanan->status !== StatusPesanan::MenungguKonfirmasi) {
            return back()->with('error', 'Hanya pesanan yang berstatus menunggu konfirmasi yang dapat disetujui hasilnya.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->update([
                'status' => StatusPesanan::HasilDisetujui,
            ]);

            // Hapus password akun game seketika sesuai aturan PRD C-11 & C11
            $pesanan->hapusPasswordAkunGame();

            $pesanan->catatRiwayat(
                StatusPesanan::MenungguKonfirmasi->value,
                StatusPesanan::HasilDisetujui->value,
                Auth::id(),
                'Customer telah memeriksa dan menyetujui hasil pengerjaan joki. Kredensial password akun game telah dihapus demi keamanan.'
            );
        });

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', 'Hasil pengerjaan berhasil disetujui! Kredensial password akun game Anda telah dihapus secara aman. Silakan klik "Konfirmasi Selesai" untuk menutup pesanan.');
    }

    /**
     * Ajukan komplain hasil pengerjaan joki oleh customer (C-12).
     * Status diubah menjadi dikomplain dan password akun game TIDAK dihapus karena Worker mungkin perlu revisi.
     */
    public function ajukanKomplain(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeOwner($pesanan);

        if ($pesanan->status !== StatusPesanan::MenungguKonfirmasi) {
            return back()->with('error', 'Komplain hanya dapat diajukan saat pesanan berstatus menunggu konfirmasi.');
        }

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'alasan.required' => 'Alasan komplain wajib diisi.',
            'alasan.min' => 'Alasan komplain minimal 10 karakter.',
            'alasan.max' => 'Alasan komplain maksimal 1000 karakter.',
        ]);

        DB::transaction(function () use ($pesanan, $validated) {
            $pesanan->update([
                'status' => StatusPesanan::Dikomplain,
            ]);

            $pesanan->catatRiwayat(
                StatusPesanan::MenungguKonfirmasi->value,
                StatusPesanan::Dikomplain->value,
                Auth::id(),
                'Komplain Customer: ' . $validated['alasan']
            );
        });

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', 'Komplain Anda berhasil dikirim ke Admin. Admin dan Worker akan segera meninjau dan melanjutkan perbaikan.');
    }

    /**
     * Konfirmasi penyelesaian pesanan secara final oleh customer (C-13).
     * Status diubah menjadi selesai dan selesai_at diisi.
     */
    public function konfirmasiSelesai(Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeOwner($pesanan);

        if ($pesanan->status !== StatusPesanan::HasilDisetujui) {
            return back()->with('error', 'Hanya pesanan yang telah disetujui hasilnya yang dapat dikonfirmasi selesai.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->update([
                'status' => StatusPesanan::Selesai,
                'selesai_at' => now(),
            ]);

            // Pastikan kembali kredensial sudah dihapus
            $pesanan->hapusPasswordAkunGame();

            $pesanan->catatRiwayat(
                StatusPesanan::HasilDisetujui->value,
                StatusPesanan::Selesai->value,
                Auth::id(),
                'Pesanan telah diselesaikan secara final oleh customer. Terima kasih telah menggunakan layanan EiraStore!'
            );
        });

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', 'Pesanan telah selesai! Terima kasih atas kepercayaan Anda pada EiraStore. Silakan berikan rating dan ulasan Anda.');
    }

    /**
     * Simpan rating dan ulasan oleh customer (C-14).
     */
    public function simpanUlasan(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeOwner($pesanan);

        if ($pesanan->status !== StatusPesanan::Selesai) {
            return back()->with('error', 'Ulasan hanya dapat diberikan untuk pesanan yang telah berstatus selesai.');
        }

        if ($pesanan->ulasan()->exists()) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk pesanan ini.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:500'],
        ], [
            'rating.required' => 'Rating bintang wajib dipilih (1-5).',
            'rating.integer' => 'Format rating tidak valid.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'komentar.max' => 'Komentar ulasan maksimal 500 karakter.',
        ]);

        $pesanan->ulasan()->create([
            'rating' => (int) $validated['rating'],
            'komentar' => $validated['komentar'] ?? null,
        ]);

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', 'Terima kasih banyak atas rating dan ulasan yang Anda berikan!');
    }

    /**
     * Tampilkan / stream screenshot bukti pengerjaan untuk Customer, Admin, atau Worker terkait.
     */
    public function lihatBuktiSelesai(Pesanan $pesanan): BinaryFileResponse
    {
        $this->authorizeView($pesanan);

        $laporan = $pesanan->laporanTerbaru;

        if (! $laporan || empty($laporan->foto_bukti) || ! Storage::disk('local')->exists($laporan->foto_bukti)) {
            abort(404, 'Foto bukti pengerjaan tidak ditemukan di server.');
        }

        return response()->file(Storage::disk('local')->path($laporan->foto_bukti));
    }

    // ── Helper Internal ───────────────────────────────────

    /**
     * Buat rekaman pesanan resmi beserta pesanan_item dan riwayat status di database.
     */
    protected function createOrderRecord(int $customerId, Layanan $layanan, array $payload, array $calcResult): Pesanan
    {
        return DB::transaction(function () use ($customerId, $layanan, $payload, $calcResult) {
            $kodePesanan = $this->calculationService->generateKodePesanan();

            $pesanan = Pesanan::create([
                'kode_pesanan' => $kodePesanan,
                'customer_id' => $customerId,
                'worker_id' => null,
                'layanan_id' => $layanan->id,
                'no_wa_customer' => $payload['no_wa_customer'] ?? null,
                'id_akun_game' => $payload['id_akun_game'],
                'login_via' => $payload['login_via'] ?? ($layanan->butuh_login_game ? null : 'Mabar / Gendong'),
                'username_akun_game' => $payload['username_akun_game'] ?? null,
                'password_akun_game' => $payload['password_akun_game'] ?? null, // terenkripsi otomatis oleh model cast jika ada
                'nickname_game' => $payload['nickname_game'] ?? null,
                'server_game' => $payload['server_game'] ?? ($payload['zone_id'] ?? null),
                'data_tambahan' => array_filter([
                    'zone_id' => $payload['zone_id'] ?? null,
                    'catatan_login' => $payload['catatan_login'] ?? null,
                ]),
                'catatan_customer' => $payload['catatan_customer'] ?? null,
                'total_harga' => $calcResult['total_harga'],
                'status' => StatusPesanan::MenungguPembayaran,
                'data_disetujui' => false,
                'batas_waktu_bayar' => now()->addHours(2), // 2 JAM KEDALUWARSA SESUAI KESEPAKATAN
                'selesai_at' => null,
                'kredensial_dihapus_at' => null,
            ]);

            // Simpan nomor WA ke profil customer jika profil belum memiliki no_hp
            $customer = User::find($customerId);
            if ($customer && empty($customer->no_hp) && ! empty($payload['no_wa_customer'])) {
                $customer->update(['no_hp' => $payload['no_wa_customer']]);
            }

            // Simpan setiap pesanan_item (salinan permanen harga & nama saat dipesan)
            foreach ($calcResult['items'] as $item) {
                $pesanan->pesananItems()->create([
                    'layanan_item_id' => $item['layanan_item_id'] ?? null,
                    'peran' => $item['peran'],
                    'nilai' => $item['nilai'] ?? null,
                    'nama_item' => $item['nama_item'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Catat status pertama ke timeline riwayat
            RiwayatStatusPesanan::create([
                'pesanan_id' => $pesanan->id,
                'status_lama' => null,
                'status_baru' => StatusPesanan::MenungguPembayaran->value,
                'catatan' => 'Pesanan berhasil dibuat, menunggu pembayaran dari customer.',
                'diubah_oleh' => $customerId,
            ]);

            return $pesanan;
        });
    }

    /**
     * Otorisasi kepemilikan pesanan (hanya pemilik).
     */
    protected function authorizeOwner(Pesanan $pesanan): void
    {
        if ((int) Auth::id() !== (int) $pesanan->customer_id) {
            abort(403, 'Anda tidak memiliki hak akses ke pesanan ini.');
        }
    }

    /**
     * Otorisasi tampilan pesanan (pemilik, admin, atau worker yang ditugaskan).
     */
    protected function authorizeView(Pesanan $pesanan): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu untuk melihat pesanan ini.');
        }

        if ($user->role === Role::Admin) {
            return;
        }

        if ($user->role === Role::Worker && (int) $pesanan->worker_id === (int) $user->id) {
            return;
        }

        if ((int) $user->id === (int) $pesanan->customer_id) {
            return;
        }

        abort(403, 'Anda tidak memiliki izin untuk melihat pesanan ini.');
    }
}
