<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\LaporanTransaksiController;
use App\Http\Controllers\Admin\LaporanWorkerController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\LayananItemController;
use App\Http\Controllers\Admin\MetodePembayaranController;
use App\Http\Controllers\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\GameCatalogController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Worker\PesananController as WorkerPesananController;
use App\Models\Game;
use App\Models\Ulasan;
use Illuminate\Support\Facades\Route;

// ── Halaman Publik ─────────────────────────────────────
Route::get('/', function () {
    $games = Game::aktif()->withCount(['layanan' => function ($query) {
        $query->where('aktif', true);
    }])->get();

    $ulasanTerbaru = Ulasan::with(['pesanan.customer', 'pesanan.layanan.game'])
        ->latest()
        ->take(5)
        ->get();

    $totalUlasan = Ulasan::count();
    $rataRataRating = $totalUlasan > 0 ? round((float) Ulasan::avg('rating'), 1) : 5.0;

    return view('beranda', compact('games', 'ulasanTerbaru', 'totalUlasan', 'rataRataRating'));
})->name('beranda');

Route::get('/game/{game}', [GameCatalogController::class, 'show'])->name('game.show');

Route::get('/lacak', function () {
    $kode = trim(request('invoice', ''));
    if ($kode !== '') {
        $pesanan = \App\Models\Pesanan::where('kode_pesanan', $kode)->first();
        if ($pesanan) {
            return redirect()->route('pesanan.show', $pesanan->kode_pesanan);
        }

        return back()->with('error', "Pesanan dengan nomor/kode '{$kode}' tidak ditemukan. Mohon periksa kembali kode pesanan Anda.");
    }

    return view('lacak');
})->name('lacak.index');

// ── Route Admin (harus login + role admin) ─────────────
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard — halaman utama Admin setelah login (A-19)
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Fase 4: Katalog Game & Layanan Joki
        Route::resource('games', GameController::class)->except(['show']);
        Route::resource('layanan', LayananController::class)->except(['show']);
        Route::resource('layanan.items', LayananItemController::class)->except(['show'])->parameters([
            'items' => 'item',
        ]);

        // Fase 5: Pengelolaan Rekening Pembayaran Dinamis
        Route::resource('metode-pembayaran', MetodePembayaranController::class)->except(['show']);

        // Fase 6: Manajemen Worker
        Route::patch('workers/{worker}/toggle-status', [WorkerController::class, 'toggleStatus'])->name('workers.toggle-status');
        Route::resource('workers', WorkerController::class)->except(['show', 'destroy']);

        // Fase 6: Verifikasi Pesanan & Penugasan Worker (A-20 s/d A-25)
        Route::get('pesanan', [AdminPesananController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/{pesanan:kode_pesanan}', [AdminPesananController::class, 'show'])->name('pesanan.show');
        Route::post('pesanan/{pesanan:kode_pesanan}/setujui-data', [AdminPesananController::class, 'setujuiData'])->name('pesanan.setujui-data');
        Route::post('pesanan/{pesanan:kode_pesanan}/minta-revisi-data', [AdminPesananController::class, 'mintaRevisiData'])->name('pesanan.minta-revisi-data');
        Route::post('pesanan/{pesanan:kode_pesanan}/setujui-pembayaran', [AdminPesananController::class, 'setujuiPembayaran'])->name('pesanan.setujui-pembayaran');
        Route::post('pesanan/{pesanan:kode_pesanan}/tolak-pembayaran', [AdminPesananController::class, 'tolakPembayaran'])->name('pesanan.tolak-pembayaran');
        Route::post('pesanan/{pesanan:kode_pesanan}/tugaskan-worker', [AdminPesananController::class, 'tugaskanWorker'])->name('pesanan.tugaskan-worker');

        // Fase 7: Laporan Worker, Verifikasi & Notifikasi Customer (A-26 s/d A-29)
        Route::get('laporan-worker', [LaporanWorkerController::class, 'index'])->name('laporan-worker.index');
        Route::post('pesanan/{pesanan:kode_pesanan}/verifikasi-laporan', [AdminPesananController::class, 'verifikasiLaporan'])->name('pesanan.verifikasi-laporan');
        Route::post('pesanan/{pesanan:kode_pesanan}/minta-revisi-hasil', [AdminPesananController::class, 'mintaRevisiHasil'])->name('pesanan.minta-revisi-hasil');
        Route::post('pesanan/{pesanan:kode_pesanan}/kirim-notifikasi-selesai', [AdminPesananController::class, 'kirimNotifikasiSelesai'])->name('pesanan.kirim-notifikasi-selesai');
        Route::get('pesanan/{pesanan:kode_pesanan}/bukti-selesai/{laporan}', [AdminPesananController::class, 'lihatBuktiSelesai'])->name('pesanan.bukti-selesai');

        // Fase 7: Laporan Transaksi & Pendapatan (A-30)
        Route::get('laporan-transaksi', [LaporanTransaksiController::class, 'index'])->name('laporan-transaksi.index');
    });

// ── Route Worker (harus login + role worker) ───────────
Route::middleware(['auth', 'role:worker'])
    ->prefix('worker')
    ->name('worker.')
    ->group(function () {
        // Daftar pesanan — halaman utama Worker setelah login (W-03)
        Route::get('/pesanan', [WorkerPesananController::class, 'index'])->name('pesanan');
        Route::get('/pesanan/{pesanan:kode_pesanan}', [WorkerPesananController::class, 'show'])->name('pesanan.show');
        Route::post('/pesanan/{pesanan:kode_pesanan}/mulai', [WorkerPesananController::class, 'mulai'])->name('pesanan.mulai');
        Route::post('/pesanan/{pesanan:kode_pesanan}/lapor', [WorkerPesananController::class, 'lapor'])->name('pesanan.lapor');
        Route::get('/pesanan/{pesanan:kode_pesanan}/bukti-selesai/{laporan}', [WorkerPesananController::class, 'lihatBukti'])->name('pesanan.bukti-selesai');
    });

// ── Form Pemesanan Publik (Bisa diakses tanpa login) ──
Route::get('/layanan/{layanan}/pesan', [PesananController::class, 'create'])->name('pesanan.create');
Route::post('/layanan/{layanan}/pesan', [PesananController::class, 'store'])->name('pesanan.store');

// ── Route Customer / Pengguna Terautentikasi ───────────
Route::middleware(['auth'])->group(function () {
    // Alur konfirmasi draf pesanan
    Route::get('/pesanan/konfirmasi-draf', [PesananController::class, 'konfirmasiDraft'])->name('pesanan.konfirmasi-draf');
    Route::post('/pesanan/konfirmasi-draf', [PesananController::class, 'prosesKonfirmasiDraft'])->name('pesanan.konfirmasi-draf.proses');
    Route::delete('/pesanan/konfirmasi-draf', [PesananController::class, 'batalkanDraft'])->name('pesanan.konfirmasi-draf.batal');

    // Pesanan saya (C-08)
    Route::get('/pesanan-saya', [PesananController::class, 'pesananSaya'])->name('pesanan.saya');

    // Detail pesanan, invoice checkout & countdown (C-08)
    Route::get('/pesanan/{pesanan:kode_pesanan}', [PesananController::class, 'show'])->name('pesanan.show');

    // Batalkan pesanan mandiri
    Route::post('/pesanan/{pesanan:kode_pesanan}/batalkan', [PesananController::class, 'batalkan'])->name('pesanan.batalkan');

    // Unggah dan unduh bukti transfer (C-09)
    Route::post('/pesanan/{pesanan:kode_pesanan}/bukti', [PesananController::class, 'unggahBukti'])->name('pesanan.bukti.unggah');
    Route::get('/pesanan/{pesanan:kode_pesanan}/bukti', [PesananController::class, 'unduhBukti'])->name('pesanan.bukti.unduh');

    // Revisi data akun game oleh customer (C-10)
    Route::post('/pesanan/{pesanan:kode_pesanan}/revisi-data', [PesananController::class, 'simpanRevisiData'])->name('pesanan.revisi-data');

    // Siklus Penyelesaian Pesanan Customer (C-11 s/d C-14)
    Route::post('/pesanan/{pesanan:kode_pesanan}/setujui-hasil', [PesananController::class, 'setujuiHasil'])->name('pesanan.setujui-hasil');
    Route::post('/pesanan/{pesanan:kode_pesanan}/ajukan-komplain', [PesananController::class, 'ajukanKomplain'])->name('pesanan.ajukan-komplain');
    Route::post('/pesanan/{pesanan:kode_pesanan}/konfirmasi-selesai', [PesananController::class, 'konfirmasiSelesai'])->name('pesanan.konfirmasi-selesai');
    Route::post('/pesanan/{pesanan:kode_pesanan}/ulasan', [PesananController::class, 'simpanUlasan'])->name('pesanan.simpan-ulasan');
    Route::get('/pesanan/{pesanan:kode_pesanan}/bukti-selesai', [PesananController::class, 'lihatBuktiSelesai'])->name('pesanan.bukti-selesai');

    // Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
