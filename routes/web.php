<?php

use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\LayananItemController;
use App\Http\Controllers\Admin\MetodePembayaranController;
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
    return view('lacak');
})->name('lacak.index');

// ── Route Admin (harus login + role admin) ─────────────
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard — halaman utama Admin setelah login
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // Fase 4: Katalog Game & Layanan Joki
        Route::resource('games', GameController::class)->except(['show']);
        Route::resource('layanan', LayananController::class)->except(['show']);
        Route::resource('layanan.items', LayananItemController::class)->except(['show'])->parameters([
            'items' => 'item',
        ]);

        // Fase 5: Pengelolaan Rekening Pembayaran Dinamis
        Route::resource('metode-pembayaran', MetodePembayaranController::class)->except(['show']);
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

    // Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
