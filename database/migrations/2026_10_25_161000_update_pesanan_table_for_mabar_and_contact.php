<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('no_wa_customer', 30)->nullable()->after('layanan_id');
            $table->string('login_via', 30)->nullable()->change();
            $table->string('username_akun_game', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('no_wa_customer');
            $table->string('login_via', 30)->nullable(false)->change();
            $table->string('username_akun_game', 100)->nullable(false)->change();
        });
    }
};
