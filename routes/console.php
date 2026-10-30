<?php

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
