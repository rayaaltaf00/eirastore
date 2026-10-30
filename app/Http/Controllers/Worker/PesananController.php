<?php

namespace App\Http\Controllers\Worker;

use App\Enums\StatusLaporan;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\LaporanPenyelesaian;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PesananController extends Controller
{
    /**
     * Tampilkan daftar pesanan yang ditugaskan kepada Worker yang sedang login (W-03).
     */
    public function index(Request $request): View
    {
        $worker = Auth::user();
        $tab = $request->get('tab', 'aktif');
        $search = $request->get('q');

        $query = Pesanan::with(['layanan.game', 'pesananItems.layananItem', 'items.layananItem', 'laporanTerbaru'])
            ->where('worker_id', $worker->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pesanan', 'like', "%{$search}%")
                    ->orWhere('id_akun_game', 'like', "%{$search}%")
                    ->orWhere('nickname_game', 'like', "%{$search}%")
                    ->orWhereHas('layanan', function ($sub) use ($search) {
                        $sub->where('nama_layanan', 'like', "%{$search}%")
                            ->orWhereHas('game', function ($g) use ($search) {
                                $g->where('nama_game', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Hitung badge counter
        $countAktif = Pesanan::where('worker_id', $worker->id)
            ->whereIn('status', [
                StatusPesanan::Diproses->value,
                StatusPesanan::SedangDikerjakan->value,
                StatusPesanan::LaporanMasuk->value,
                StatusPesanan::WorkerSelesai->value,
                StatusPesanan::MenungguKonfirmasi->value,
                StatusPesanan::Dikomplain->value,
                StatusPesanan::PerluRevisiHasil->value,
            ])->count();

        $countPerluRevisi = Pesanan::where('worker_id', $worker->id)
            ->where('status', StatusPesanan::PerluRevisiHasil->value)
            ->count();

        $countSelesai = Pesanan::where('worker_id', $worker->id)
            ->where('status', StatusPesanan::Selesai->value)
            ->count();

        // Terapkan filter tab
        if ($tab === 'revisi') {
            $query->where('status', StatusPesanan::PerluRevisiHasil->value);
        } elseif ($tab === 'selesai') {
            $query->whereIn('status', [
                StatusPesanan::Selesai->value,
                StatusPesanan::Dibatalkan->value,
            ]);
        } else {
            // Default: tab aktif
            $query->whereIn('status', [
                StatusPesanan::Diproses->value,
                StatusPesanan::SedangDikerjakan->value,
                StatusPesanan::LaporanMasuk->value,
                StatusPesanan::WorkerSelesai->value,
                StatusPesanan::MenungguKonfirmasi->value,
                StatusPesanan::Dikomplain->value,
                StatusPesanan::PerluRevisiHasil->value,
            ]);
        }

        $daftarPesanan = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('worker.pesanan.index', compact(
            'daftarPesanan',
            'tab',
            'search',
            'countAktif',
            'countPerluRevisi',
            'countSelesai'
        ));
    }

    /**
     * Tampilkan detail pesanan joki untuk Worker (W-03 & W-04).
     */
    public function show(Pesanan $pesanan): View
    {
        $this->authorizeWorker($pesanan);

        $pesanan->load([
            'customer',
            'layanan.game',
            'pesananItems.layananItem',
            'items.layananItem',
            'laporanPenyelesaian.verifikator',
            'riwayatStatus.pengubah',
        ]);

        // Cari catatan revisi admin terbaru jika status memerlukan revisi
        $catatanRevisiTerbaru = null;
        if (in_array($pesanan->status, [StatusPesanan::PerluRevisiHasil, StatusPesanan::Dikomplain])) {
            $riwayatRevisi = $pesanan->riwayatStatus()
                ->where('status_baru', StatusPesanan::PerluRevisiHasil->value)
                ->latest('id')
                ->first();
            $catatanRevisiTerbaru = $riwayatRevisi?->catatan;
        }

        return view('worker.pesanan.show', compact('pesanan', 'catatanRevisiTerbaru'));
    }

    /**
     * Aksi Worker mulai pengerjaan joki di in-game (W-04).
     * Mengubah status dari 'diproses' menjadi 'sedang_dikerjakan'.
     */
    public function mulai(Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeWorker($pesanan);

        if ($pesanan->status !== StatusPesanan::Diproses) {
            return back()->with('error', 'Status pesanan tidak valid untuk memulai pengerjaan (harus berstatus "Diproses").');
        }

        $statusLama = $pesanan->status->value;
        $pesanan->update([
            'status' => StatusPesanan::SedangDikerjakan,
        ]);

        $pesanan->catatRiwayat(
            $statusLama,
            StatusPesanan::SedangDikerjakan->value,
            Auth::id(),
            'Worker mulai login dan mengerjakan joki in-game.'
        );

        return back()->with('success', 'Status pengerjaan berhasil diaktifkan! Silakan kerjakan pesanan sesuai target dengan profesional.');
    }

    /**
     * Aksi Worker mengunggah foto bukti penyelesaian (W-05 & W-06).
     * Mengubah status menjadi 'laporan_masuk'.
     */
    public function lapor(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorizeWorker($pesanan);

        if (! in_array($pesanan->status, [StatusPesanan::SedangDikerjakan, StatusPesanan::PerluRevisiHasil])) {
            return back()->with('error', 'Pesanan tidak dalam status yang dapat dilaporkan hasilnya.');
        }

        $request->validate([
            'foto_bukti' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'foto_bukti.required' => 'Foto bukti screenshot hasil joki wajib diunggah.',
            'foto_bukti.image' => 'Berkas bukti harus berupa gambar.',
            'foto_bukti.mimes' => 'Format gambar yang didukung: JPG, JPEG, PNG, atau WebP.',
            'foto_bukti.max' => 'Ukuran berkas maksimal 2 MB.',
            'catatan.max' => 'Catatan ringkasan pengerjaan maksimal 1.000 karakter.',
        ]);

        $path = $request->file('foto_bukti')->store('bukti_selesai', 'local');

        $laporan = LaporanPenyelesaian::create([
            'pesanan_id' => $pesanan->id,
            'worker_id' => Auth::id(),
            'foto_bukti' => $path,
            'catatan' => $request->input('catatan'),
            'status' => StatusLaporan::MenungguVerifikasi,
        ]);

        $statusLama = $pesanan->status->value;
        $pesanan->update([
            'status' => StatusPesanan::LaporanMasuk,
        ]);

        $isRevisi = ($statusLama === StatusPesanan::PerluRevisiHasil->value);
        $keteranganRiwayat = $isRevisi
            ? 'Worker mengirimkan bukti revisi hasil pengerjaan.'
            : 'Worker mengunggah foto bukti penyelesaian joki.';

        if ($request->filled('catatan')) {
            $keteranganRiwayat .= ' Catatan: "' . $request->input('catatan') . '"';
        }

        $pesanan->catatRiwayat(
            $statusLama,
            StatusPesanan::LaporanMasuk->value,
            Auth::id(),
            $keteranganRiwayat
        );

        return back()->with('success', 'Laporan penyelesaian berhasil dikirimkan! Menunggu verifikasi dari Admin sebelum diteruskan ke pelanggan.');
    }

    /**
     * Tampilkan / stream berkas foto bukti penyelesaian untuk Worker yang berhak.
     */
    public function lihatBukti(Pesanan $pesanan, LaporanPenyelesaian $laporan): StreamedResponse
    {
        $this->authorizeWorker($pesanan);

        if ($laporan->pesanan_id !== $pesanan->id) {
            abort(404, 'Laporan tidak sesuai dengan pesanan.');
        }

        if (! Storage::disk('local')->exists($laporan->foto_bukti)) {
            abort(404, 'Berkas foto bukti tidak ditemukan di server.');
        }

        return Storage::disk('local')->response($laporan->foto_bukti);
    }

    /**
     * Helper otorisasi: pastikan pesanan ditugaskan ke worker yang sedang login.
     */
    protected function authorizeWorker(Pesanan $pesanan): void
    {
        if ((int) $pesanan->worker_id !== (int) Auth::id()) {
            abort(403, 'Akses ditolak. Pesanan ini tidak ditugaskan kepada Anda.');
        }
    }
}
