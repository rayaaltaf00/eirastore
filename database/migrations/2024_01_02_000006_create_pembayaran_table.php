<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->string('metode_pembayaran', 50);         // nama bank atau e-wallet (BCA, Dana, dll)
            $table->string('bukti_transfer');                 // path file bukti di storage privat
            $table->string('status', 30)->default('menunggu_verifikasi'); // menunggu_verifikasi, disetujui, ditolak
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->restrictOnDelete(); // Admin yang memverifikasi
            $table->dateTime('diverifikasi_at')->nullable();
            $table->timestamps();

            $table->unique('pesanan_id');                     // satu pembayaran per pesanan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
