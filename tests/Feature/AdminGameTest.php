<?php

namespace Tests\Feature;

use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminGameTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_guest_cannot_access_game_crud(): void
    {
        $this->get(route('admin.games.index'))->assertRedirect(route('login'));
        $this->get(route('admin.games.create'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_game_crud(): void
    {
        $this->actingAs($this->customer)->get(route('admin.games.index'))->assertStatus(403);
    }

    public function test_admin_can_view_games_index(): void
    {
        $game = Game::create([
            'nama_game' => 'Apex Legends',
            'kategori'  => 'FPS',
            'gambar'    => 'games/apex.jpg',
            'aktif'     => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.games.index'));

        $response->assertStatus(200);
        $response->assertSee('Apex Legends');
    }

    public function test_admin_can_view_create_game_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.games.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Game Baru');
    }

    public function test_admin_can_store_game_with_image_url(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.games.store'), [
            'nama_game'     => 'Honor of Kings Test',
            'kategori'      => 'MOBA',
            'deskripsi'     => 'Game MOBA 5v5 seru',
            'gambar_sumber' => 'url',
            'gambar_url'    => 'https://images.unsplash.com/photo-1542751371-adc38448a05e',
            'aktif'         => '1',
        ]);

        $response->assertRedirect(route('admin.games.index'));
        $this->assertDatabaseHas('games', [
            'nama_game' => 'Honor of Kings Test',
            'kategori'  => 'MOBA',
            'gambar'    => 'https://images.unsplash.com/photo-1542751371-adc38448a05e',
        ]);
    }

    public function test_admin_can_store_game_with_uploaded_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('dota2.jpg', 600, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.games.store'), [
            'nama_game'     => 'Dota 2',
            'kategori'      => 'MOBA',
            'deskripsi'     => 'Defense of the Ancients 2',
            'gambar_sumber' => 'upload',
            'gambar_upload' => $file,
            'aktif'         => '1',
        ]);

        $response->assertRedirect(route('admin.games.index'));
        
        $game = Game::where('nama_game', 'Dota 2')->first();
        $this->assertNotNull($game);
        $this->assertStringStartsWith('games/', $game->gambar);
        Storage::disk('public')->assertExists($game->gambar);
    }

    public function test_game_name_must_be_unique(): void
    {
        Game::create(['nama_game' => 'Valorant Test', 'gambar' => 'games/val.jpg', 'aktif' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.games.store'), [
            'nama_game'     => 'Valorant Test',
            'gambar_sumber' => 'url',
        ]);

        $response->assertSessionHasErrors('nama_game');
    }

    public function test_admin_can_update_game(): void
    {
        $game = Game::create([
            'nama_game' => 'Fortnite',
            'kategori'  => 'BR',
            'gambar'    => 'games/fn.jpg',
            'aktif'     => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.games.update', $game), [
            'nama_game'     => 'Fortnite Battle Royale',
            'kategori'      => 'Battle Royale',
            'deskripsi'     => 'Game survival seru',
            'gambar_sumber' => 'keep',
            'aktif'         => '0',
        ]);

        $response->assertRedirect(route('admin.games.index'));
        $this->assertDatabaseHas('games', [
            'id'        => $game->id,
            'nama_game' => 'Fortnite Battle Royale',
            'aktif'     => 0,
        ]);
    }

    public function test_admin_can_delete_game_without_services(): void
    {
        $game = Game::create(['nama_game' => 'Game To Delete', 'gambar' => 'games/del.jpg', 'aktif' => true]);

        $response = $this->actingAs($this->admin)->delete(route('admin.games.destroy', $game));

        $response->assertRedirect(route('admin.games.index'));
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    public function test_admin_cannot_delete_game_if_services_exist(): void
    {
        $game = Game::create(['nama_game' => 'Game With Service', 'gambar' => 'games/srv.jpg', 'aktif' => true]);
        
        Layanan::create([
            'game_id'      => $game->id,
            'nama_layanan' => 'Joki Rank',
            'tipe_harga'   => TipeHarga::Tangga,
            'aktif'        => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.games.destroy', $game));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }

    public function test_admin_cannot_store_near_duplicate_game_name(): void
    {
        Game::create([
            'nama_game' => 'Mobile Legends: Bang Bang',
            'kategori'  => 'MOBA',
            'gambar'    => 'games/mlbb.jpg',
            'aktif'     => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.games.store'), [
            'nama_game'     => 'Mobile Legend',
            'kategori'      => 'MOBA',
            'gambar_sumber' => 'url',
            'gambar_url'    => 'https://example.com/ml.jpg',
            'aktif'         => '1',
        ]);

        $response->assertSessionHasErrors(['nama_game']);
        $this->assertDatabaseMissing('games', ['nama_game' => 'Mobile Legend']);
    }

    public function test_admin_cannot_store_typo_similar_game_name(): void
    {
        Game::create([
            'nama_game' => 'Valorant',
            'kategori'  => 'FPS',
            'gambar'    => 'games/val.jpg',
            'aktif'     => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.games.store'), [
            'nama_game'     => 'Valoran',
            'kategori'      => 'FPS',
            'gambar_sumber' => 'url',
            'gambar_url'    => 'https://example.com/val.jpg',
            'aktif'         => '1',
        ]);

        $response->assertSessionHasErrors(['nama_game']);
        $this->assertDatabaseMissing('games', ['nama_game' => 'Valoran']);
    }

    public function test_admin_can_update_game_keeping_same_name(): void
    {
        $game = Game::create([
            'nama_game' => 'Genshin Impact',
            'kategori'  => 'RPG',
            'gambar'    => 'games/genshin.jpg',
            'aktif'     => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.games.update', $game), [
            'nama_game'     => 'Genshin Impact',
            'kategori'      => 'RPG Open World',
            'deskripsi'     => 'Updated deskripsi',
            'gambar_sumber' => 'url',
            'gambar_url'    => 'https://example.com/genshin.jpg',
            'aktif'         => '1',
        ]);

        $response->assertRedirect(route('admin.games.index'));
        $this->assertDatabaseHas('games', [
            'id'       => $game->id,
            'kategori' => 'RPG Open World',
        ]);
    }
}
