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
use App\Models\User;
use App\Services\OrderCalculationService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
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
            session(['draft_pesanan' => $payload]);

            return redirect()->route('login')
                ->with('info', 'Draf pesanan Anda telah tersimpan! Silakan masuk atau daftar akun untuk melanjutkan pembayaran.');
        }

        // Pastikan hanya role customer yang membuat pesanan
        $user = Auth::user();
        if ($user->role !== Role::Customer) {
            return back()->withInput()->with('error', 'Hanya akun customer yang dapat membuat pesanan joki.');
        }

        // PROTEKSI ANTI-SPAM: Cek apakah masih ada pesanan menunggu_pembayaran yang aktif
        $pendingOrder = Pesanan::query()
            ->where('customer_id', $user->id)
            ->where('status', StatusPesanan::MenungguPembayaran)
            ->where('batas_waktu_bayar', '>', now())
            ->first();

        if ($pendingOrder) {
            return redirect()->route('pesanan.show', $pendingOrder->kode_pesanan)
                ->with('error', "Anda masih memiliki pesanan {$pendingOrder->kode_pesanan} yang menunggu pembayaran. Silakan selesaikan pembayaran tersebut atau batalkan terlebih dahulu sebelum membuat pesanan baru.");
        }

        // Buat pesanan secara resmi di database
        $pesanan = $this->createOrderRecord($user->id, $layanan, $payload, $calcResult);

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

        $user = Auth::user();
        if ($user->role !== Role::Customer) {
            return redirect()->route('beranda')->with('error', 'Hanya akun customer yang dapat membuat pesanan.');
        }

        // Cek proteksi anti-spam
        $pendingOrder = Pesanan::query()
            ->where('customer_id', $user->id)
            ->where('status', StatusPesanan::MenungguPembayaran)
            ->where('batas_waktu_bayar', '>', now())
            ->first();

        if ($pendingOrder) {
            session()->forget('draft_pesanan');

            return redirect()->route('pesanan.show', $pendingOrder->kode_pesanan)
                ->with('error', "Anda masih memiliki pesanan {$pendingOrder->kode_pesanan} yang menunggu pembayaran.");
        }

        /** @var Layanan $layanan */
        $layanan = Layanan::findOrFail($draft['layanan_id']);

        try {
            $calcResult = $this->calculationService->hitung($layanan, $draft);
        } catch (Exception $e) {
            session()->forget('draft_pesanan');

            return redirect()->route('game.show', $layanan->game)->with('error', $e->getMessage());
        }

        $pesanan = $this->createOrderRecord($user->id, $layanan, $draft, $calcResult);

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
            'riwayatStatus' => fn ($q) => $q->orderBy('created_at'),
            'customer',
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

        $validated = $request->validate([
            'id_akun_game'        => ['required', 'string', 'max:100'],
            'login_via'           => ['nullable', 'string', 'max:30'],
            'username_akun_game'  => ['nullable', 'string', 'max:100'],
            'password_akun_game'  => ['nullable', 'string', 'min:4', 'max:255'],
            'nickname_game'       => ['nullable', 'string', 'max:100'],
            'server_game'         => ['nullable', 'string', 'max:50'],
            'zone_id'             => ['nullable', 'string', 'max:50'],
            'no_wa_customer'      => ['nullable', 'string', 'max:30'],
            'catatan_customer'    => ['nullable', 'string', 'max:1000'],
        ], [
            'id_akun_game.required' => 'ID Akun game wajib diisi.',
            'password_akun_game.min' => 'Password minimal 4 karakter.',
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

        $pesanan->update($updatePayload);

        $pesanan->catatRiwayat(
            StatusPesanan::PerluRevisiData->value,
            StatusPesanan::MenungguVerifikasi->value,
            Auth::id(),
            'Customer telah memperbarui data akun game. Menunggu verifikasi ulang oleh Admin.'
        );

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('success', 'Data akun game berhasil diperbarui! Admin akan segera memverifikasi pesanan Anda.');
    }

    // ── Helper Internal ───────────────────────────────────

    /**
     * Buat rekaman pesanan resmi beserta pesanan_item dan riwayat status di database.
     */
    protected function createOrderRecord(int $customerId, Layanan $layanan, array $payload, array $calcResult): Pesanan
    {
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
    }

    /**
     * Otorisasi kepemilikan pesanan (hanya pemilik).
     */
    protected function authorizeOwner(Pesanan $pesanan): void
    {
        if (Auth::id() !== $pesanan->customer_id) {
            abort(403, 'Anda tidak memiliki hak akses ke pesanan ini.');
        }
    }

    /**
     * Otorisasi tampilan pesanan (pemilik, admin, atau worker yang ditugaskan).
     */
    protected function authorizeView(Pesanan $pesanan): void
    {
        $user = Auth::user();

        if ($user->role === Role::Admin) {
            return;
        }

        if ($user->role === Role::Worker && $pesanan->worker_id === $user->id) {
            return;
        }

        if ($user->id === $pesanan->customer_id) {
            return;
        }

        abort(403, 'Anda tidak memiliki izin untuk melihat pesanan ini.');
    }
}
