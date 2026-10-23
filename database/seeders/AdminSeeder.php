<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk membuat akun Admin default.
 * Jalankan: php artisan db:seed --class=AdminSeeder
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buat akun Admin hanya jika belum ada (aman dijalankan berulang)
        User::firstOrCreate(
            ['email' => 'admin@eirastore.com'],
            [
                'nama'        => 'Administrator',
                'no_hp'       => '081200000000',
                'password'    => Hash::make('password123'),
                'role'        => 'admin',
                'status_akun' => 'aktif',
            ]
        );
    }
}
