<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard utama Admin beserta kartu metrik per status pesanan.
     * Referensi: A-19
     */
    public function index(): View
    {
        // 11 Status pesanan penting sesuai PRD A-19
        $statusCounts = [
            'menunggu_verifikasi' => Pesanan::where('status', StatusPesanan::MenungguVerifikasi)->count(),
            'perlu_revisi_data'   => Pesanan::where('status', StatusPesanan::PerluRevisiData)->count(),
            'siap_ditugaskan'     => Pesanan::where('status', StatusPesanan::SiapDitugaskan)->count(),
            'diproses'            => Pesanan::where('status', StatusPesanan::Diproses)->count(),
            'sedang_dikerjakan'   => Pesanan::where('status', StatusPesanan::SedangDikerjakan)->count(),
            'laporan_masuk'       => Pesanan::where('status', StatusPesanan::LaporanMasuk)->count(),
            'worker_selesai'      => Pesanan::where('status', StatusPesanan::WorkerSelesai)->count(),
            'menunggu_konfirmasi' => Pesanan::where('status', StatusPesanan::MenungguKonfirmasi)->count(),
            'dikomplain'          => Pesanan::where('status', StatusPesanan::Dikomplain)->count(),
            'perlu_revisi_hasil'  => Pesanan::where('status', StatusPesanan::PerluRevisiHasil)->count(),
            'selesai'             => Pesanan::where('status', StatusPesanan::Selesai)->count(),
        ];

        // Ringkasan metrik global
        $totalPesanan = Pesanan::count();

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
