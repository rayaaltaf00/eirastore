<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('nama_game', 100)->unique();   // nama game harus unik
            $table->text('deskripsi')->nullable();
            $table->string('gambar');                      // path file gambar di storage
            $table->boolean('aktif')->default(true);       // false = tidak tampil di katalog publik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
