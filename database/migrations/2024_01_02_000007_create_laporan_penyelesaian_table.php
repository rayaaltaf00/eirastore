<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_penyelesaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->foreignId('worker_id')->constrained('users')->restrictOnDelete();
            $table->string('foto_bukti');                    // path file foto bukti di storage privat
            $table->string('status', 30)->default('menunggu_verifikasi'); // menunggu_verifikasi, terverifikasi, ditolak
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('diverifikasi_at')->nullable();
            $table->timestamps();
            // Satu pesanan bisa punya banyak laporan (laporan awal + revisi), jadi TIDAK unique pesanan_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_penyelesaian');
    }
};
