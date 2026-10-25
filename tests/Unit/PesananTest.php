<?php

namespace Tests\Unit;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananTest extends TestCase
{
    use RefreshDatabase;

    public function test_pesanan_casts_and_password_encryption(): void
    {
        $pesanan = new Pesanan;
        $pesanan->kode_pesanan = 'ES-20261025-001';
        $pesanan->id_akun_game = '12345678';
        $pesanan->login_via = 'Moonton';
        $pesanan->username_akun_game = 'player@example.com';
        $pesanan->password_akun_game = 'SecretPass123!';
        $pesanan->nickname_game = 'ProPlayer';
        $pesanan->total_harga = '150000.00';
        $pesanan->status = StatusPesanan::MenungguPembayaran;
        $pesanan->data_tambahan = ['catatan' => 'Fast track'];
        $pesanan->data_disetujui = true;

        // Cek bahwa accessor mengembalikan password asli
        $this->assertEquals('SecretPass123!', $pesanan->password_akun_game);

        // Cek cast status
        $this->assertInstanceOf(StatusPesanan::class, $pesanan->status);
        $this->assertEquals(StatusPesanan::MenungguPembayaran, $pesanan->status);

        // Cek cast data_tambahan
        $this->assertIsArray($pesanan->data_tambahan);
        $this->assertEquals('Fast track', $pesanan->data_tambahan['catatan']);
    }
}
