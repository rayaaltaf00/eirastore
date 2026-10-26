<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\LayananItemController;
use App\Http\Controllers\Admin\MetodePembayaranController;
use App\Http\Controllers\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\GameCatalogController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Models\Game;
use Illuminate\Support\Facades\Route;

// ── Halaman Publik ─────────────────────────────────────
Route::get('/', function () {
    $games = Game::aktif()->get();

    return view('beranda', compact('games'));
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
    });

// ── Route Worker (harus login + role worker) ───────────
Route::middleware(['auth', 'role:worker'])
    ->prefix('worker')
    ->name('worker.')
    ->group(function () {
        // Daftar pesanan — halaman utama Worker setelah login
        Route::get('/pesanan', function () {
            return view('worker.pesanan');
        })->name('pesanan');

        // Route lainnya akan ditambahkan di fase berikutnya
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

    // Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
