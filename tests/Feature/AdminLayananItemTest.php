<?php

namespace Tests\Feature;

use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\LayananItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayananItemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Layanan $layananTangga;
    private Layanan $layananProgres;
    private Layanan $layananPilihan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);

        $game = Game::create(['nama_game' => 'Mobile Legends', 'gambar' => 'games/ml.jpg', 'aktif' => true]);

        $this->layananTangga = Layanan::create([
            'game_id'      => $game->id,
            'nama_layanan' => 'Rank Tangga',
            'tipe_harga'   => TipeHarga::Tangga,
            'aktif'        => true,
        ]);

        $this->layananProgres = Layanan::create([
            'game_id'      => $game->id,
            'nama_layanan' => 'Explore Progres',
            'tipe_harga'   => TipeHarga::Progres,
            'aktif'        => true,
        ]);

        $this->layananPilihan = Layanan::create([
            'game_id'      => $game->id,
            'nama_layanan' => 'Paket Pilihan',
            'tipe_harga'   => TipeHarga::Pilihan,
            'aktif'        => true,
        ]);
    }

    public function test_tangga_item_requires_nilai_maks(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.layanan.items.store', $this->layananTangga),
            [
                'jenis'      => 'utama',
                'nama'       => 'Grandmaster',
                'urutan'     => 1,
                'harga'      => 5000,
                'nilai_maks' => null,
            ]
        );

        $response->assertSessionHasErrors('nilai_maks');
    }

    public function test_tangga_item_enforces_unique_urutan(): void
    {
        LayananItem::create([
            'layanan_id' => $this->layananTangga->id,
            'nama'       => 'Grandmaster',
            'urutan'     => 1,
            'harga'      => 5000,
            'nilai_maks' => 5,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.layanan.items.store', $this->layananTangga),
            [
                'jenis'      => 'utama',
                'nama'       => 'Epic',
                'urutan'     => 1,
                'harga'      => 7000,
                'nilai_maks' => 5,
            ]
        );

        $response->assertSessionHasErrors('urutan');
    }

    public function test_progres_parent_item_must_have_price_zero(): void
    {
        // Region induk pada tipe progres harus harga 0
        $response = $this->actingAs($this->admin)->post(
            route('admin.layanan.items.store', $this->layananProgres),
            [
                'parent_id'  => null,
                'jenis'      => 'utama',
                'nama'       => 'Mondstadt',
                'urutan'     => 1,
                'harga'      => 20000, // Harga harus 0
                'nilai_maks' => null,
            ]
        );

        $response->assertSessionHasErrors('harga');
    }

    public function test_progres_sub_wilayah_can_be_created_under_parent(): void
    {
        $parent = LayananItem::create([
            'layanan_id' => $this->layananProgres->id,
            'nama'       => 'Mondstadt',
            'urutan'     => 1,
            'harga'      => 0,
            'parent_id'  => null,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.layanan.items.store', $this->layananProgres),
            [
                'parent_id'  => $parent->id,
                'jenis'      => 'utama',
                'nama'       => 'Starfell Valley',
                'urutan'     => 1,
                'harga'      => 35000,
                'satuan'     => 'persen',
            ]
        );

        $response->assertRedirect(route('admin.layanan.items.index', $this->layananProgres));
        $this->assertDatabaseHas('layanan_item', [
            'layanan_id' => $this->layananProgres->id,
            'parent_id'  => $parent->id,
            'nama'       => 'Starfell Valley',
            'harga'      => 35000,
        ]);
    }

    public function test_pilihan_satuan_and_nilai_maks_pairing(): void
    {
        // Jika satuan diisi, nilai_maks juga harus diisi
        $response = $this->actingAs($this->admin)->post(
            route('admin.layanan.items.store', $this->layananPilihan),
            [
                'jenis'      => 'utama',
                'nama'       => 'Paket Quest',
                'urutan'     => 1,
                'harga'      => 25000,
                'satuan'     => 'chapter',
                'nilai_maks' => null,
            ]
        );

        $response->assertSessionHasErrors('nilai_maks');
    }

    public function test_admin_can_delete_layanan_item(): void
    {
        $item = LayananItem::create([
            'layanan_id' => $this->layananTangga->id,
            'nama'       => 'Warrior',
            'urutan'     => 1,
            'harga'      => 3000,
            'nilai_maks' => 3,
        ]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.layanan.items.destroy', [$this->layananTangga, $item])
        );

        $response->assertRedirect(route('admin.layanan.items.index', $this->layananTangga));
        $this->assertDatabaseMissing('layanan_item', ['id' => $item->id]);
    }
}
