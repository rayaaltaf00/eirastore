<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\StatusLaporan;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\LaporanPenyelesaian;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller Pesanan (Sisi Panel Admin)
 *
 * Mengendalikan alur operasional toko dan verifikasi transaksi:
 * 1. Filter antrean kerja (pesanan yang butuh tindakan verifikasi pembayaran, penugasan, atau laporan)
 * 2. Verifikasi ganda: Persetujuan Data Akun Game & Persetujuan Mutasi Pembayaran
 * 3. Permintaan revisi data akun ke pelanggan jika data login/ID tidak cocok
 * 4. Penolakan bukti pembayaran palsu (otomatis membatalkan pesanan & memusnahkan password)
 * 5. Penugasan pesanan ke Worker aktif yang siap bekerja
 * 6. Audit hasil pengerjaan (verifikasi screenshot pengerjaan vs revisi pengerjaan)
 * 7. Perlindungan Privasi: Password akun game pelanggan disembunyikan dari antarmuka Admin
 */
class PesananController extends Controller
{
    /**
     * Tampilkan daftar pesanan masuk dengan filter dan pencarian.
     * Referensi: PRD A-20
     */
    public function index(Request $request): View
    {
        $query = Pesanan::with(['customer', 'layanan.game', 'pembayaran', 'worker']);

        // Filter status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        } elseif ($request->status === 'semua' || $request->filter === 'semua') {
            // Tampilkan semua status tanpa batas
        } else {
            // Tampilan awal (default): menyaring pesanan yang membutuhkan tindakan Admin (A-20)
            $query->whereIn('status', [
                StatusPesanan::MenungguVerifikasi,
                StatusPesanan::SiapDitugaskan,
                StatusPesanan::LaporanMasuk,
                StatusPesanan::WorkerSelesai,
                StatusPesanan::Dikomplain,
            ]);
        }

        // Filter status pembayaran spesifik
        if ($request->filled('pembayaran') && in_array($request->pembayaran, ['menunggu_verifikasi', 'disetujui', 'ditolak'])) {
            $query->whereHas('pembayaran', function ($q) use ($request) {
                $q->where('status', $request->pembayaran);
            });
        }

        // Pencarian berdasarkan kode_pesanan, nama customer, atau no wa
        if ($request->filled('q')) {
            $keyword = str_replace(['%', '_'], ['\%', '\_'], trim($request->q));
            $query->where(function ($q) use ($keyword) {
                $q->where('kode_pesanan', 'like', "%{$keyword}%")
                    ->orWhere('no_wa_customer', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', function ($cq) use ($keyword) {
                        $cq->where('nama', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        $pesananList = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.pesanan.index', compact('pesananList'));
    }

    /**
     * Tampilkan detail lengkap pesanan untuk verifikasi Admin.
     * Catatan Keamanan PRD A-20: Password akun game TIDAK DITAMPILKAN kepada Admin demi privasi pelanggan.
     */
    public function show(Pesanan $pesanan): View
    {
        $pesanan->load([
            'customer',
            'worker',
            'layanan.game',
            'pesananItems',
            'pembayaran.verifikator',
            'riwayatStatus.pengubah',
            'laporanPenyelesaian.worker',
        ]);

        // Daftar worker aktif yang siap menerima penugasan (A-25)
        $workersAktif = User::worker()
            ->where('status_akun', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('admin.pesanan.show', compact('pesanan', 'workersAktif'));
    }

    /**
     * Setujui data akun dan rincian pemesanan customer (A-21).
     * Jika pembayaran sudah berstatus disetujui, alur otomatis berubah ke siap_ditugaskan.
     */
    public function setujuiData(Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::MenungguVerifikasi || $pesanan->data_disetujui) {
            return back()->with('error', 'Status pesanan saat ini tidak valid untuk persetujuan data akun.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->data_disetujui = true;
            $statusLama = $pesanan->status;

            // Jika bukti pembayaran sudah disetujui sebelumnya, otomatis siap ditugaskan
            if ($pesanan->pembayaran && $pesanan->pembayaran->status === StatusPembayaran::Disetujui) {
                $pesanan->status = StatusPesanan::SiapDitugaskan;
                $pesanan->save();

                $pesanan->catatRiwayat(
                    $statusLama->value,
                    StatusPesanan::SiapDitugaskan->value,
                    Auth::id(),
                    'Data pemesanan disetujui. Pembayaran telah valid, pesanan siap ditugaskan ke worker.'
                );
            } else {
                $pesanan->save();

                $pesanan->catatRiwayat(
                    $statusLama->value,
                    $pesanan->status->value,
                    Auth::id(),
                    'Data pemesanan disetujui oleh Admin. Menunggu verifikasi bukti transfer.'
                );
            }
        });

        return back()->with('success', 'Data akun pemesanan berhasil disetujui.');
    }

    /**
     * Minta customer merevisi data akun game jika terdapat kesalahan (A-22).
     */
    public function mintaRevisiData(Request $request, Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::MenungguVerifikasi) {
            return back()->with('error', 'Status pesanan tidak valid untuk meminta revisi data.');
        }

        $request->validate([
            'catatan' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan.required' => 'Catatan revisi wajib diisi untuk menginstruksikan pelanggan.',
            'catatan.min' => 'Catatan revisi minimal 5 karakter.',
            'catatan.max' => 'Catatan revisi maksimal 1000 karakter.',
        ]);

        DB::transaction(function () use ($request, $pesanan) {
            $statusLama = $pesanan->status;

            $pesanan->update([
                'status' => StatusPesanan::PerluRevisiData,
                'data_disetujui' => false,
            ]);

            $pesanan->catatRiwayat(
                $statusLama->value,
                StatusPesanan::PerluRevisiData->value,
                Auth::id(),
                "Permintaan Revisi Data: {$request->catatan}"
            );
        });

        return back()->with('success', 'Permintaan revisi data akun berhasil dikirimkan ke pelanggan.');
    }

    /**
     * Setujui bukti transfer pembayaran customer (A-23).
     * Jika data akun game juga sudah disetujui, alur otomatis berubah ke siap_ditugaskan.
     */
    public function setujuiPembayaran(Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::MenungguVerifikasi ||
            ! $pesanan->pembayaran ||
            $pesanan->pembayaran->status !== StatusPembayaran::MenungguVerifikasi) {
            return back()->with('error', 'Status pesanan atau pembayaran tidak valid untuk disetujui.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->pembayaran->update([
                'status' => StatusPembayaran::Disetujui,
                'diverifikasi_oleh' => Auth::id(),
                'diverifikasi_at' => now(),
            ]);

            $statusLama = $pesanan->status;

            // Jika data akun game juga sudah disetujui, otomatis siap ditugaskan ke worker
            if ($pesanan->data_disetujui) {
                $pesanan->status = StatusPesanan::SiapDitugaskan;
                $pesanan->save();

                $pesanan->catatRiwayat(
                    $statusLama->value,
                    StatusPesanan::SiapDitugaskan->value,
                    Auth::id(),
                    'Bukti pembayaran disetujui. Data akun telah valid, pesanan siap ditugaskan ke worker.'
                );
            } else {
                $pesanan->catatRiwayat(
                    $statusLama->value,
                    $pesanan->status->value,
                    Auth::id(),
                    'Bukti pembayaran disetujui oleh Admin. Menunggu persetujuan data akun game.'
                );
            }
        });

        return back()->with('success', 'Bukti pembayaran berhasil disetujui.');
    }

    /**
     * Tolak bukti pembayaran jika palsu atau tidak sesuai (A-24).
     * Pesanan langsung dibatalkan (terminal) dan password akun game dihapus seketika.
     */
    public function tolakPembayaran(Request $request, Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::MenungguVerifikasi ||
            ! $pesanan->pembayaran ||
            $pesanan->pembayaran->status !== StatusPembayaran::MenungguVerifikasi) {
            return back()->with('error', 'Status pesanan atau pembayaran tidak valid untuk ditolak.');
        }

        $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'alasan.required' => 'Alasan penolakan pembayaran wajib diisi.',
            'alasan.min' => 'Alasan penolakan minimal 5 karakter.',
            'alasan.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        DB::transaction(function () use ($request, $pesanan) {
            $pesanan->pembayaran->update([
                'status' => StatusPembayaran::Ditolak,
                'diverifikasi_oleh' => Auth::id(),
                'diverifikasi_at' => now(),
            ]);

            $statusLama = $pesanan->status;

            $pesanan->update([
                'status' => StatusPesanan::Dibatalkan,
            ]);

            // Hapus password akun game seketika (A-24 Mandate)
            $pesanan->hapusPasswordAkunGame();

            $pesanan->catatRiwayat(
                $statusLama->value,
                StatusPesanan::Dibatalkan->value,
                Auth::id(),
                "Bukti pembayaran ditolak: {$request->alasan}"
            );
        });

        return back()->with('success', 'Bukti pembayaran ditolak. Pesanan telah dibatalkan dan password akun pelanggan otomatis dihapus.');
    }

    /**
     * Tugaskan pesanan yang sudah berstatus siap_ditugaskan ke worker terpilih (A-25).
     */
    public function tugaskanWorker(Request $request, Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::SiapDitugaskan) {
            return back()->with('error', 'Pesanan belum berada pada status Siap Ditugaskan.');
        }

        $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
        ], [
            'worker_id.required' => 'Silakan tentukan worker yang akan ditugaskan.',
            'worker_id.exists' => 'Worker yang dipilih tidak terdaftar di sistem.',
        ]);

        $worker = User::where('id', $request->worker_id)
            ->where('role', Role::Worker)
            ->where('status_akun', 'aktif')
            ->first();

        if (! $worker) {
            return back()->with('error', 'Worker yang dipilih tidak berstatus aktif atau bukan bertindak sebagai worker.');
        }

        $statusLama = $pesanan->status;

        DB::transaction(function () use ($pesanan, $worker, $statusLama) {
            $pesanan->update([
                'worker_id' => $worker->id,
                'status' => StatusPesanan::Diproses,
            ]);

            $pesanan->catatRiwayat(
                $statusLama->value,
                StatusPesanan::Diproses->value,
                Auth::id(),
                "Ditugaskan ke {$worker->nama}"
            );
        });

        return back()->with('success', "Pesanan berhasil ditugaskan kepada worker {$worker->nama}.");
    }

    /**
     * Verifikasi laporan penyelesaian dari Worker (A-27).
     * Mengubah status laporan menjadi terverifikasi dan status pesanan menjadi worker_selesai.
     */
    public function verifikasiLaporan(Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::LaporanMasuk) {
            return back()->with('error', 'Status pesanan tidak valid untuk verifikasi laporan.');
        }

        $laporan = $pesanan->laporanPenyelesaian()
            ->where('status', StatusLaporan::MenungguVerifikasi)
            ->latest()
            ->first();

        if (! $laporan) {
            return back()->with('error', 'Tidak ditemukan laporan penyelesaian yang menunggu verifikasi.');
        }

        DB::transaction(function () use ($pesanan, $laporan) {
            $laporan->update([
                'status' => StatusLaporan::Terverifikasi,
                'diverifikasi_oleh' => Auth::id(),
                'diverifikasi_at' => now(),
            ]);

            $statusLama = $pesanan->status;

            $pesanan->update([
                'status' => StatusPesanan::WorkerSelesai,
            ]);

            $pesanan->catatRiwayat(
                $statusLama->value,
                StatusPesanan::WorkerSelesai->value,
                Auth::id(),
                'Laporan penyelesaian pengerjaan diverifikasi oleh Admin. Status pesanan: Worker Selesai.'
            );
        });

        return back()->with('success', 'Laporan penyelesaian berhasil diverifikasi. Pesanan kini siap diberitahukan ke pelanggan.');
    }

    /**
     * Minta revisi hasil pengerjaan ke Worker (A-29).
     * Berlaku saat pesanan berstatus laporan_masuk (laporan ditolak) atau dikomplain (jalur komplain customer).
     */
    public function mintaRevisiHasil(Request $request, Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::LaporanMasuk && $pesanan->status !== StatusPesanan::Dikomplain) {
            return back()->with('error', 'Status pesanan saat ini tidak memenuhi syarat untuk meminta revisi hasil.');
        }

        $request->validate([
            'catatan' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan.required' => 'Catatan revisi untuk worker wajib diisi.',
            'catatan.min' => 'Catatan revisi minimal 5 karakter.',
            'catatan.max' => 'Catatan revisi maksimal 1000 karakter.',
        ]);

        DB::transaction(function () use ($request, $pesanan) {
            // Pada jalur laporan_masuk, ubah status laporan terbaru menjadi ditolak
            if ($pesanan->status === StatusPesanan::LaporanMasuk) {
                $laporan = $pesanan->laporanPenyelesaian()
                    ->where('status', StatusLaporan::MenungguVerifikasi)
                    ->latest()
                    ->first();

                if ($laporan) {
                    $laporan->update([
                        'status' => StatusLaporan::Ditolak,
                        'diverifikasi_oleh' => Auth::id(),
                        'diverifikasi_at' => now(),
                    ]);
                }
            }

            $statusLama = $pesanan->status;

            $pesanan->update([
                'status' => StatusPesanan::PerluRevisiHasil,
            ]);

            $pesanan->catatRiwayat(
                $statusLama->value,
                StatusPesanan::PerluRevisiHasil->value,
                Auth::id(),
                "Permintaan Revisi Hasil untuk Worker: {$request->catatan}"
            );
        });

        return back()->with('success', 'Permintaan revisi hasil berhasil dikirim ke Worker.');
    }

    /**
     * Kirim notifikasi penyelesaian ke pelanggan (A-28).
     * Mengubah status pesanan menjadi menunggu_konfirmasi.
     */
    public function kirimNotifikasiSelesai(Request $request, Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status !== StatusPesanan::WorkerSelesai) {
            return back()->with('error', 'Pesanan harus berstatus Worker Selesai sebelum mengirim notifikasi.');
        }

        $request->validate([
            'pesan_singkat' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $pesanan) {
            $statusLama = $pesanan->status;

            $pesanan->update([
                'status' => StatusPesanan::MenungguKonfirmasi,
            ]);

            $catatan = $request->filled('pesan_singkat')
                ? "Notifikasi penyelesaian diteruskan ke pelanggan: {$request->pesan_singkat}"
                : 'Pemberitahuan penyelesaian telah dikirimkan kepada pelanggan. Menunggu konfirmasi penerimaan hasil.';

            $pesanan->catatRiwayat(
                $statusLama->value,
                StatusPesanan::MenungguKonfirmasi->value,
                Auth::id(),
                $catatan
            );
        });

        return back()->with('success', 'Pemberitahuan penyelesaian berhasil dikirimkan ke pelanggan.');
    }

    /**
     * Stream file foto bukti laporan penyelesaian dari storage privat untuk Admin.
     */
    public function lihatBuktiSelesai(Pesanan $pesanan, LaporanPenyelesaian $laporan): BinaryFileResponse
    {
        if ($laporan->pesanan_id !== $pesanan->id) {
            abort(404, 'Bukti laporan tidak cocok dengan pesanan.');
        }

        if (! Storage::disk('local')->exists($laporan->foto_bukti)) {
            abort(404, 'File bukti pengerjaan tidak ditemukan di penyimpanan server.');
        }

        return response()->file(Storage::disk('local')->path($laporan->foto_bukti));
    }
}
