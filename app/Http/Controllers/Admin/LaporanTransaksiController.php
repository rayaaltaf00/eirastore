<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Laporan Transaksi & Keuangan (Sisi Panel Admin)
 *
 * Mengelola rekapitulasi data pendapatan toko dari pesanan yang telah tuntas:
 * 1. Filter rentang waktu kustom (harian, bulanan, tahunan)
 * 2. Agregasi total pendapatan dan nilai rata-rata transaksi di level database
 * 3. Pagination terkontrol untuk mencegah pemborosan memori server
 */
class LaporanTransaksiController extends Controller
{
    /**
     * Tampilkan laporan transaksi dan pendapatan pesanan selesai (A-30).
     */
    public function index(Request $request): View
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_akhir.date' => 'Format tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
        ]);

        $tanggalMulai = $request->filled('tanggal_mulai')
            ? Carbon::parse($request->input('tanggal_mulai'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->input('tanggal_akhir'))->endOfDay()
            : Carbon::now()->endOfDay();

        $query = Pesanan::with(['customer', 'layanan.game', 'worker'])
            ->where('status', StatusPesanan::Selesai)
            ->where(function ($q) use ($tanggalMulai, $tanggalAkhir) {
                $q->whereBetween('selesai_at', [$tanggalMulai, $tanggalAkhir])
                    ->orWhere(function ($sub) use ($tanggalMulai, $tanggalAkhir) {
                        $sub->whereNull('selesai_at')
                            ->whereBetween('updated_at', [$tanggalMulai, $tanggalAkhir]);
                    });
            })
            ->orderByRaw('COALESCE(selesai_at, updated_at) DESC');

        // Hitung agregat transaksi langsung di level database agar hemat memori
        $agregat = (clone $query)->selectRaw('COUNT(*) as total_pesanan, COALESCE(SUM(total_harga), 0) as total_pendapatan')->first();
        $totalPesanan = (int) ($agregat->total_pesanan ?? 0);
        $totalPendapatan = (int) ($agregat->total_pendapatan ?? 0);
        $rataRataTransaksi = $totalPesanan > 0 ? (int) round($totalPendapatan / $totalPesanan) : 0;

        $perPage = $request->input('per_page', 25);
        $perPage = min(max(1, (int) $perPage), 100); // Batasi 1-100 untuk mencegah memory exhaustion
        $pesananList = $query->paginate($perPage)->withQueryString();

        return view('admin.laporan-transaksi.index', [
            'pesananList' => $pesananList,
            'tanggalMulai' => $tanggalMulai->format('Y-m-d'),
            'tanggalAkhir' => $tanggalAkhir->format('Y-m-d'),
            'totalPesanan' => $totalPesanan,
            'totalPendapatan' => $totalPendapatan,
            'rataRataTransaksi' => $rataRataTransaksi,
        ]);
    }
}
