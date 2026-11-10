<?php

namespace Tests\Unit;

use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\LayananItem;
use App\Services\OrderCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderCalculationService $service;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderCalculationService;
        $this->game = Game::create([
            'nama_game' => 'Mobile Legends: Bang Bang',
            'kategori' => 'MOBA',
            'gambar' => 'mlbb.jpg',
            'aktif' => true,
        ]);
    }

    public function test_tangga_calculation_same_tier(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Push Rank MLBB',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        $epic = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Epic',
            'jenis' => 'utama',
            'harga' => 10000,
            'satuan' => 'bintang',
            'nilai_maks' => 5,
            'urutan' => 1,
            'aktif' => true,
        ]);

        // Dari Epic 1 bintang ke Epic 4 bintang: selisih 3 bintang = 3 * 10.000 = 30.000
        $result = $this->service->hitung($layanan, [
            'tier_awal_id' => $epic->id,
            'bintang_awal' => 1,
            'tier_tujuan_id' => $epic->id,
            'bintang_tujuan' => 4,
        ]);

        $this->assertEquals(30000, $result['total_harga']);
        $this->assertCount(2, $result['items']);

        // Item pertama: posisi awal (subtotal 0, hanya penanda)
        $this->assertEquals('awal', $result['items'][0]['peran']);
        $this->assertEquals(0, $result['items'][0]['subtotal']);

        // Item kedua: target tujuan (subtotal = harga sebenarnya)
        $this->assertEquals('tujuan', $result['items'][1]['peran']);
        $this->assertEquals(30000, $result['items'][1]['subtotal']);
    }

    public function test_tangga_calculation_cross_tier_with_intermediate(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Push Rank MLBB Multi Tier',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        // Epic (maks 5 bintang, harga 10.000)
        $epic = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Epic',
            'jenis' => 'utama',
            'harga' => 10000,
            'satuan' => 'bintang',
            'nilai_maks' => 5,
            'urutan' => 1,
            'aktif' => true,
        ]);

        // Legend (maks 5 bintang, harga 15.000) - tier antara
        $legend = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Legend',
            'jenis' => 'utama',
            'harga' => 15000,
            'satuan' => 'bintang',
            'nilai_maks' => 5,
            'urutan' => 2,
            'aktif' => true,
        ]);

        // Mythic (harga 25.000) - tier tujuan
        $mythic = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Mythic',
            'jenis' => 'utama',
            'harga' => 25000,
            'satuan' => 'bintang',
            'nilai_maks' => 25,
            'urutan' => 3,
            'aktif' => true,
        ]);

        // Dari Epic 3 bintang (sisa 2 bintang = 2 * 10.000 = 20.000)
        // Lewat Legend (penuh 5 bintang = 5 * 15.000 = 75.000)
        // Sampai Mythic 10 bintang (10 * 25.000 = 250.000)
        // Total = 20.000 + 75.000 + 250.000 = 345.000
        $result = $this->service->hitung($layanan, [
            'tier_awal_id' => $epic->id,
            'bintang_awal' => 3,
            'tier_tujuan_id' => $mythic->id,
            'bintang_tujuan' => 10,
        ]);

        $this->assertEquals(345000, $result['total_harga']);
        $this->assertCount(3, $result['items']);
    }

    public function test_tangga_calculation_cross_tier_when_starting_at_max_stars(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Push Rank MLBB Edge Case Max Stars',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        // Mythical Honor (maks 25 bintang, harga 35.000)
        $honor = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Mythical Honor',
            'jenis' => 'utama',
            'harga' => 35000,
            'satuan' => 'bintang',
            'nilai_maks' => 25,
            'urutan' => 1,
            'aktif' => true,
        ]);

        // Mythical Glory (harga 45.000)
        $glory = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Mythical Glory',
            'jenis' => 'utama',
            'harga' => 45000,
            'satuan' => 'bintang',
            'nilai_maks' => 50,
            'urutan' => 2,
            'aktif' => true,
        ]);

        // Dari Mythical Honor 25 bintang (bintang awal = nilai_maks, langkahAwal = 0)
        // Menuju Mythical Glory 50 bintang (50 * 45.000 = 2.250.000)
        // Item awal harus TETAP DIBUAT sebagai penanda dengan subtotal 0!
        $result = $this->service->hitung($layanan, [
            'tier_awal_id' => $honor->id,
            'bintang_awal' => 25,
            'tier_tujuan_id' => $glory->id,
            'bintang_tujuan' => 50,
        ]);

        $this->assertEquals(2250000, $result['total_harga']);
        $this->assertCount(2, $result['items']);

        // Item 0: awal (penanda posisi awal)
        $this->assertEquals('awal', $result['items'][0]['peran']);
        $this->assertEquals('Mythical Honor (25 ★)', $result['items'][0]['nama_item']);
        $this->assertEquals(0, $result['items'][0]['subtotal']);

        // Item 1: tujuan
        $this->assertEquals('tujuan', $result['items'][1]['peran']);
        $this->assertEquals('Mythical Glory (Target 50 ★)', $result['items'][1]['nama_item']);
        $this->assertEquals(2250000, $result['items'][1]['subtotal']);
    }

    public function test_progres_calculation(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Eksplorasi Genshin',
            'tipe_harga' => TipeHarga::Progres,
            'aktif' => true,
        ]);

        $region = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Dragonspine',
            'jenis' => 'utama',
            'harga' => 60000, // harga dasar 100%
            'satuan' => 'persen',
            'urutan' => 1,
            'aktif' => true,
        ]);

        // Customer memiliki progres 40%, sisa 60%: 60/100 * 60.000 = 36.000
        $result = $this->service->hitung($layanan, [
            'progres' => [
                $region->id => 40,
            ],
        ]);

        $this->assertEquals(36000, $result['total_harga']);
        $this->assertCount(1, $result['items']);
    }

    public function test_pilihan_calculation_with_quantity(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Match Classic',
            'tipe_harga' => TipeHarga::Pilihan,
            'aktif' => true,
        ]);

        $paket = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Paket 5 Match Win',
            'jenis' => 'utama',
            'harga' => 35000,
            'satuan' => 'paket',
            'nilai_maks' => 5,
            'urutan' => 1,
            'aktif' => true,
        ]);

        $result = $this->service->hitung($layanan, [
            'items' => [
                $paket->id => 2, // 2 paket = 2 * 35.000 = 70.000
            ],
        ]);

        $this->assertEquals(70000, $result['total_harga']);
    }

    public function test_generate_kode_pesanan_format(): void
    {
        $kode1 = $this->service->generateKodePesanan();
        // Format baru: ES-YYYYMMDD-XXX-ABCD (dengan 4 karakter random anti-enumerasi)
        $this->assertMatchesRegularExpression('/^ES-\d{8}-001-[A-F0-9]{4}$/', $kode1);
    }
}
