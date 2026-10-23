<?php

use Illuminate\Support\Facades\Route;

// ── Halaman Publik ─────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('beranda');

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
    // Route customer akan ditambahkan di fase berikutnya
});

require __DIR__.'/auth.php';
