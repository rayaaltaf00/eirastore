<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->string('status_lama', 30)->nullable();   // NULL untuk entri pertama (pesanan baru dibuat)
            $table->string('status_baru', 30);
            $table->text('catatan')->nullable();              // catatan revisi, alasan tolak, dll
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->restrictOnDelete(); // NULL = sistem (scheduler)
            $table->timestamp('created_at')->nullable();      // hanya created_at, tidak perlu updated_at

            $table->index(['pesanan_id', 'created_at']);      // untuk timeline di halaman detail
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_pesanan');
    }
};
