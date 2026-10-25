<?php

namespace Tests\Feature;

use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\LayananItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_active_game_profile(): void
    {
        $game = Game::create([
            'nama_game' => 'Mobile Legends',
            'kategori'  => 'MOBA',
            'deskripsi' => 'Game MOBA terpopuler di Indonesia',
            'gambar'    => 'games/ml.jpg',
            'aktif'     => true,
        ]);

        $layanan = Layanan::create([
            'game_id'      => $game->id,
            'nama_layanan' => 'Joki Rank Bintang',
            'tipe_harga'   => TipeHarga::Tangga,
            'aktif'        => true,
        ]);

        LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama'       => 'Grandmaster',
            'harga'      => 7000,
            'nilai_maks' => 5,
            'urutan'     => 1,
            'aktif'      => true,
        ]);

        $response = $this->get(route('game.show', $game));

        $response->assertStatus(200);
        $response->assertSee('Mobile Legends');
        $response->assertSee('Joki Rank Bintang');
        $response->assertSee('7.000');
    }

    public function test_inactive_game_returns_404(): void
    {
        $game = Game::create([
            'nama_game' => 'Game Nonaktif',
            'gambar'    => 'games/off.jpg',
            'aktif'     => false,
        ]);

        $response = $this->get(route('game.show', $game));

        $response->assertStatus(404);
    }

    public function test_landing_page_displays_active_games(): void
    {
        Game::create(['nama_game' => 'Valorant Aktif', 'gambar' => 'games/val.jpg', 'aktif' => true]);
        Game::create(['nama_game' => 'Game Tersembunyi', 'gambar' => 'games/hide.jpg', 'aktif' => false]);

        $response = $this->get(route('beranda'));

        $response->assertStatus(200);
        $response->assertSee('Valorant Aktif');
        $response->assertDontSee('Game Tersembunyi');
    }
}
