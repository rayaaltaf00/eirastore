<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained('layanan')->cascadeOnDelete(); // hapus layanan = hapus semua itemnya
            $table->foreignId('parent_id')->nullable()->constrained('layanan_item')->cascadeOnDelete(); // untuk hierarki (misal: region → wilayah)
            $table->string('jenis', 20)->default('utama');  // utama = item inti, tambahan = item opsional (misal: pilih hero)
            $table->string('kategori', 50)->nullable();     // pengelompokan tampilan (misal: Archon Quest, World Quest)
            $table->string('nama', 150);                    // nama item (misal: "Epic", "Mondstadt", "Quest ABC")
            $table->integer('urutan')->default(0);           // urutan tampil, juga urutan tingkat untuk tipe tangga
            $table->decimal('harga', 12, 2)->default(0);    // harga per langkah (tangga), harga dasar 100% (progres), atau harga satuan (pilihan)
            $table->unsignedInteger('nilai_maks')->nullable(); // tangga: jumlah bintang per tingkat; pilihan: jumlah maks jika ada satuan
            $table->string('satuan', 20)->nullable();        // satuan jumlah (misal: "hari", "hero")
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['layanan_id', 'urutan']);         // untuk query item berurutan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan_item');
    }
};
