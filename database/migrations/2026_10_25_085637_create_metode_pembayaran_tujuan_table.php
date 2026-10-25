<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metode_pembayaran_tujuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_metode', 60);         // BCA, Mandiri, Dana, GoPay, QRIS EiraStore
            $table->string('kategori', 20);            // bank, ewallet, qris
            $table->string('nomor_rekening', 60)->nullable(); // nomor rekening / nomor e-wallet (null untuk QRIS)
            $table->string('atas_nama', 100);          // nama pemilik rekening / pemilik merchant
            $table->string('logo')->nullable();        // path gambar logo di storage atau URL
            $table->string('gambar_qris')->nullable(); // path gambar QRIS untuk di-scan
            $table->text('petunjuk_transfer')->nullable(); // instruksi transfer opsional
            $table->integer('urutan')->default(0);     // urutan tampilan
            $table->boolean('aktif')->default(true);   // status aktif/gangguan
            $table->timestamps();

            $table->index(['aktif', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metode_pembayaran_tujuan');
    }
};
