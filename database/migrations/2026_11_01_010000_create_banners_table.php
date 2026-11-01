<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 100);                    // Judul utama banner
            $table->string('subjudul', 255)->nullable();      // Teks pendukung di bawah judul
            $table->string('gambar');                          // Path file gambar banner (storage lokal)
            $table->unsignedTinyInteger('urutan')->default(0); // Urutan tampil di carousel
            $table->boolean('is_aktif')->default(true);       // Tampilkan di landing page atau tidak
            $table->timestamps();

            $table->index(['is_aktif', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
