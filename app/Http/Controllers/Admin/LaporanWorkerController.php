<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\LaporanPenyelesaian;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanWorkerController extends Controller
{
    /**
     * Tampilkan tabel daftar laporan penyelesaian dari Worker (A-26).
     * Filter default: menunggu_verifikasi.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'menunggu_verifikasi');

        $query = LaporanPenyelesaian::with([
            'pesanan.customer',
            'pesanan.layanan.game',
            'worker',
            'verifikator',
        ]);

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['menunggu_verifikasi', 'terverifikasi', 'ditolak'])) {
            $query->where('status', $statusFilter);
        }

        if ($request->filled('q')) {
            $keyword = str_replace(['%', '_'], ['\%', '\_'], trim($request->q));
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('pesanan', function ($pq) use ($keyword) {
                    $pq->where('kode_pesanan', 'like', "%{$keyword}%")
                       ->orWhereHas('layanan.game', function ($gq) use ($keyword) {
                           $gq->where('nama_game', 'like', "%{$keyword}%");
                       });
                })->orWhereHas('worker', function ($wq) use ($keyword) {
                    $wq->where('nama', 'like', "%{$keyword}%");
                });
            });
        }

        $laporanList = $query->orderByDesc('id')->paginate(15)->withQueryString();

        // Hitung badge counter status dalam 1 query agregat
        $rawCounts = LaporanPenyelesaian::selectRaw('
            COUNT(CASE WHEN status = ? THEN 1 END) as menunggu_verifikasi,
            COUNT(CASE WHEN status = ? THEN 1 END) as terverifikasi,
            COUNT(CASE WHEN status = ? THEN 1 END) as ditolak,
            COUNT(*) as semua
        ', [
            StatusLaporan::MenungguVerifikasi->value,
            StatusLaporan::Terverifikasi->value,
            StatusLaporan::Ditolak->value,
        ])->first();

        $counts = [
            'menunggu_verifikasi' => (int) ($rawCounts->menunggu_verifikasi ?? 0),
            'terverifikasi'       => (int) ($rawCounts->terverifikasi ?? 0),
            'ditolak'             => (int) ($rawCounts->ditolak ?? 0),
            'semua'               => (int) ($rawCounts->semua ?? 0),
        ];

        return view('admin.laporan-worker.index', compact('laporanList', 'counts', 'statusFilter'));
    }
}
