<?php

/**
 * Konfigurasi Artisan Console & Scheduled Background Tasks (Cron)
 *
 * Mengatur eksekusi tugas berkala otomatis:
 * 1. pesanan:cek-kedaluwarsa (setiap 5 menit): Membatalkan pesanan unpaid > 2 jam & hapus kredensial
 * 2. pesanan:auto-selesai (setiap 1 jam): Menyelesaikan otomatis pesanan yang didiamkan > 24 jam
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler untuk memeriksa pesanan kedaluwarsa setiap 5 menit
Schedule::command('pesanan:cek-kedaluwarsa')->everyFiveMinutes()->withoutOverlapping();

// Scheduler untuk menyelesaikan otomatis pesanan yang menunggu konfirmasi lebih dari 24 jam
Schedule::command('pesanan:auto-selesai')->hourly()->withoutOverlapping();
