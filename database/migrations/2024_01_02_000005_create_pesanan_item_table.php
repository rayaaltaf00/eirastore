<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->foreignId('layanan_item_id')->nullable()->constrained('layanan_item')->nullOnDelete(); // NULL jika item dihapus, data salinan tetap ada
            $table->string('peran', 20);                    // awal, tujuan, dipilih, tambahan — menandai fungsi item dalam pesanan
            $table->decimal('nilai', 10, 2)->nullable();     // bintang saat ini (tangga), persen progres (progres), jumlah (pilihan)
            $table->string('nama_item', 150);                // salinan nama item saat dipesan (tidak berubah walau item diubah)
            $table->decimal('harga_satuan', 12, 2);          // salinan harga saat dipesan
            $table->decimal('subtotal', 12, 2);              // hasil perhitungan harga untuk item ini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_item');
    }
};
