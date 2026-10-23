<?php

use Illuminate\Support\Facades\Route;

// ── Halaman Publik ─────────────────────────────────────
Route::get('/', function () {
    $games = \App\Models\Game::aktif()->get();
    return view('beranda', compact('games'));
})->name('beranda');

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

        // Route CRUD lainnya akan ditambahkan di fase berikutnya
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

// ── Route Customer (harus login) ──────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/pesanan-saya', function () {
        return view('customer.pesanan');
    })->name('pesanan.saya');
});

require __DIR__.'/auth.php';
