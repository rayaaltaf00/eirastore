<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan', 20)->unique();   // format: ES-YYYYMMDD-XXX
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('worker_id')->nullable()->constrained('users')->restrictOnDelete(); // diisi saat Admin menugaskan
            $table->foreignId('layanan_id')->constrained('layanan')->restrictOnDelete();

            // Data akun game customer — diisi saat memesan
            $table->string('id_akun_game', 100);
            $table->string('login_via', 30);                // wajib: Moonton, Facebook, Google, Apple ID, HoYoverse, Lainnya
            $table->string('username_akun_game', 100);
            $table->text('password_akun_game')->nullable();  // terenkripsi dengan encrypt(), NULL setelah dihapus
            $table->string('nickname_game', 100)->nullable();
            $table->string('server_game', 50)->nullable();
            $table->json('data_tambahan')->nullable();       // zone_id, catatan_login (data tidak sensitif)
            $table->text('catatan_customer')->nullable();     // catatan bebas untuk Worker

            // Harga dan status
            $table->decimal('total_harga', 12, 2);
            $table->string('status', 30)->default('menunggu_pembayaran'); // lihat Enum StatusPesanan
            $table->boolean('data_disetujui')->default(false); // true setelah Admin setujui data akun game
            $table->dateTime('batas_waktu_bayar');            // created_at + 24 jam
            $table->dateTime('selesai_at')->nullable();       // diisi saat status = selesai
            $table->dateTime('kredensial_dihapus_at')->nullable(); // diisi saat password akun game dihapus
            $table->timestamps();

            // Index untuk query yang sering dipakai
            $table->index(['status', 'batas_waktu_bayar']);  // untuk scheduler kedaluwarsa
            $table->index(['worker_id', 'status']);           // untuk halaman Worker
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
