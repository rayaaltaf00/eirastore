<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->restrictOnDelete(); // tidak bisa hapus game yang masih punya layanan
            $table->string('nama_layanan', 100);
            $table->text('deskripsi')->nullable();
            $table->string('tipe_harga', 20);              // tangga, progres, pilihan — menentukan cara hitung harga
            $table->string('estimasi_waktu', 50)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['game_id', 'nama_layanan']);    // nama layanan unik per game
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
