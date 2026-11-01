<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\View\View;

/**
 * Controller Dasbor Administrator
 *
 * Menyajikan statistik operasional toko secara real-time:
 * 1. Agregasi status pesanan dalam 1 query efisien
 * 2. Hitungan pesanan mendesak yang butuh tindakan Admin segera
 * 3. Kalkulasi total omzet pendapatan dari pesanan yang sukses/selesai
 * 4. Metrik ketersediaan worker aktif dan antrean transaksi terkini
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard utama Admin beserta kartu metrik per status pesanan.
     * Referensi: A-19
     */
    public function index(): View
    {
        // Hitung semua status pesanan dalam 1 query agregat efisien (menggantikan 11 query terpisah)
        $rawCounts = Pesanan::selectRaw('
            COUNT(CASE WHEN status = ? THEN 1 END) as menunggu_verifikasi,
            COUNT(CASE WHEN status = ? THEN 1 END) as perlu_revisi_data,
            COUNT(CASE WHEN status = ? THEN 1 END) as siap_ditugaskan,
            COUNT(CASE WHEN status = ? THEN 1 END) as diproses,
            COUNT(CASE WHEN status = ? THEN 1 END) as sedang_dikerjakan,
            COUNT(CASE WHEN status = ? THEN 1 END) as laporan_masuk,
            COUNT(CASE WHEN status = ? THEN 1 END) as worker_selesai,
            COUNT(CASE WHEN status = ? THEN 1 END) as menunggu_konfirmasi,
            COUNT(CASE WHEN status = ? THEN 1 END) as dikomplain,
            COUNT(CASE WHEN status = ? THEN 1 END) as perlu_revisi_hasil,
            COUNT(CASE WHEN status = ? THEN 1 END) as selesai,
            COUNT(*) as total
        ', [
            StatusPesanan::MenungguVerifikasi->value,
            StatusPesanan::PerluRevisiData->value,
            StatusPesanan::SiapDitugaskan->value,
            StatusPesanan::Diproses->value,
            StatusPesanan::SedangDikerjakan->value,
            StatusPesanan::LaporanMasuk->value,
            StatusPesanan::WorkerSelesai->value,
            StatusPesanan::MenungguKonfirmasi->value,
            StatusPesanan::Dikomplain->value,
            StatusPesanan::PerluRevisiHasil->value,
            StatusPesanan::Selesai->value,
        ])->first();

        $statusCounts = [
            'menunggu_verifikasi' => (int) ($rawCounts->menunggu_verifikasi ?? 0),
            'perlu_revisi_data' => (int) ($rawCounts->perlu_revisi_data ?? 0),
            'siap_ditugaskan' => (int) ($rawCounts->siap_ditugaskan ?? 0),
            'diproses' => (int) ($rawCounts->diproses ?? 0),
            'sedang_dikerjakan' => (int) ($rawCounts->sedang_dikerjakan ?? 0),
            'laporan_masuk' => (int) ($rawCounts->laporan_masuk ?? 0),
            'worker_selesai' => (int) ($rawCounts->worker_selesai ?? 0),
            'menunggu_konfirmasi' => (int) ($rawCounts->menunggu_konfirmasi ?? 0),
            'dikomplain' => (int) ($rawCounts->dikomplain ?? 0),
            'perlu_revisi_hasil' => (int) ($rawCounts->perlu_revisi_hasil ?? 0),
            'selesai' => (int) ($rawCounts->selesai ?? 0),
        ];

        // Ringkasan metrik global
        $totalPesanan = (int) ($rawCounts->total ?? 0);

        // Pesanan yang membutuhkan tindakan Admin segera
        $butuhTindakanCount = Pesanan::whereIn('status', [
            StatusPesanan::MenungguVerifikasi,
            StatusPesanan::SiapDitugaskan,
            StatusPesanan::LaporanMasuk,
            StatusPesanan::WorkerSelesai,
            StatusPesanan::Dikomplain,
        ])->count();

        // Total pendapatan dari pesanan yang selesai / disetujui hasilnya
        $totalPendapatan = (float) Pesanan::whereIn('status', [
            StatusPesanan::Selesai,
            StatusPesanan::HasilDisetujui,
        ])->sum('total_harga');

        // Jumlah worker aktif
        $totalWorkerAktif = User::where('role', Role::Worker)
            ->where('status_akun', 'aktif')
            ->count();

        // 5 Pesanan terbaru yang membutuhkan tindakan admin segera
        $pesananPerluTindakan = Pesanan::with(['customer', 'layanan.game', 'pembayaran'])
            ->whereIn('status', [
                StatusPesanan::MenungguVerifikasi,
                StatusPesanan::SiapDitugaskan,
                StatusPesanan::LaporanMasuk,
                StatusPesanan::WorkerSelesai,
                StatusPesanan::Dikomplain,
            ])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'statusCounts',
            'totalPesanan',
            'butuhTindakanCount',
            'totalPendapatan',
            'totalWorkerAktif',
            'pesananPerluTindakan'
        ));
    }
}
