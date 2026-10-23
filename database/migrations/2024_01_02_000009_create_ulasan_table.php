<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->unsignedTinyInteger('rating');            // 1 sampai 5 (divalidasi di aplikasi)
            $table->text('komentar')->nullable();             // komentar opsional, maks 500 karakter
            $table->timestamps();

            $table->unique('pesanan_id');                     // satu ulasan per pesanan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};
