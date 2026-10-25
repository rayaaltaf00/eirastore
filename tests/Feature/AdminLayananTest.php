<?php

namespace Tests\Feature;

use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\LayananItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminLayananTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->game = Game::create(['nama_game' => 'Mobile Legends', 'kategori' => 'MOBA', 'gambar' => 'games/ml.jpg', 'aktif' => true]);
    }

    public function test_admin_can_view_layanan_index(): void
    {
        Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank Bintang',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.layanan.index'));

        $response->assertStatus(200);
        $response->assertSee('Joki Rank Bintang');
    }

    public function test_admin_can_view_create_layanan_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.layanan.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Layanan Joki');
    }

    public function test_admin_can_store_layanan(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.layanan.store'), [
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Classic Winrate',
            'tipe_harga' => 'pilihan',
            'deskripsi' => 'Joki winrate mantap',
            'estimasi_waktu' => '1 - 2 Hari',
            'aktif' => '1',
        ]);

        $layanan = Layanan::where('nama_layanan', 'Joki Classic Winrate')->first();
        $this->assertNotNull($layanan);
        $response->assertRedirect(route('admin.layanan.items.index', $layanan));
        $this->assertDatabaseHas('layanan', [
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Classic Winrate',
            'tipe_harga' => 'pilihan',
        ]);
    }

    public function test_layanan_name_must_be_unique_per_game(): void
    {
        Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.layanan.store'), [
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank',
            'tipe_harga' => 'tangga',
        ]);

        $response->assertSessionHasErrors('nama_layanan');
    }

    public function test_admin_can_update_layanan(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank Tier',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.layanan.update', $layanan), [
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank Tier Update',
            'tipe_harga' => 'tangga',
            'deskripsi' => 'Deskripsi baru',
            'estimasi_waktu' => '3 Hari',
            'aktif' => '0',
        ]);

        $response->assertRedirect(route('admin.layanan.index', ['game_id' => $this->game->id]));
        $this->assertDatabaseHas('layanan', [
            'id' => $layanan->id,
            'nama_layanan' => 'Joki Rank Tier Update',
            'aktif' => 0,
        ]);
    }

    public function test_admin_cannot_change_tipe_harga_if_items_exist(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank Bertingkat',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Grandmaster',
            'harga' => 5000,
            'nilai_maks' => 5,
            'urutan' => 1,
            'aktif' => true,
        ]);

        // Kirim request ubah tipe_harga menjadi progres
        $response = $this->actingAs($this->admin)->put(route('admin.layanan.update', $layanan), [
            'nama_layanan' => 'Joki Rank Bertingkat',
            'tipe_harga' => 'progres',
        ]);

        // tipe_harga di database harus tetap tangga
        $layanan->refresh();
        $this->assertEquals(TipeHarga::Tangga, $layanan->tipe_harga);
    }

    public function test_admin_can_delete_layanan_and_cascades_items(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Layanan to Delete',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        $item = LayananItem::create([
            'layanan_id' => $layanan->id,
            'nama' => 'Warrior',
            'harga' => 3000,
            'nilai_maks' => 3,
            'urutan' => 1,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.layanan.destroy', $layanan));

        $response->assertRedirect(route('admin.layanan.index', ['game_id' => $this->game->id]));
        $this->assertDatabaseMissing('layanan', ['id' => $layanan->id]);
        $this->assertDatabaseMissing('layanan_item', ['id' => $item->id]);
    }

    public function test_admin_can_store_layanan_with_image_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('poster.jpg', 600, 800);

        $response = $this->actingAs($this->admin)->post(route('admin.layanan.store'), [
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Mythic Immortal',
            'tipe_harga' => 'tangga',
            'gambar_upload' => $file,
            'aktif' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $layanan = Layanan::where('nama_layanan', 'Joki Mythic Immortal')->first();
        $this->assertNotNull($layanan);
        $this->assertNotNull($layanan->gambar);
        Storage::disk('public')->assertExists($layanan->gambar);
    }

    public function test_admin_can_store_layanan_with_image_url(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.layanan.store'), [
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Classic URL',
            'tipe_harga' => 'pilihan',
            'gambar_url' => 'https://example.com/poster.jpg',
            'aktif' => '1',
        ]);

        $layanan = Layanan::where('nama_layanan', 'Joki Classic URL')->first();
        $this->assertNotNull($layanan);
        $this->assertEquals('https://example.com/poster.jpg', $layanan->gambar);
        $this->assertEquals('https://example.com/poster.jpg', $layanan->gambar_url);
    }

    public function test_layanan_gambar_url_falls_back_to_game_gambar_url(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Layanan Tanpa Gambar',
            'tipe_harga' => TipeHarga::Tangga,
            'gambar' => null,
            'aktif' => true,
        ]);

        $this->assertNotNull($layanan->gambar_url);
        $this->assertEquals($this->game->gambar_url, $layanan->gambar_url);
    }

    public function test_admin_can_reset_custom_image_to_fallback_game_image(): void
    {
        $layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Layanan Reset Gambar',
            'tipe_harga' => TipeHarga::Pilihan,
            'gambar' => 'https://example.com/custom-poster.jpg',
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.layanan.update', $layanan), [
            'game_id' => $this->game->id,
            'nama_layanan' => 'Layanan Reset Gambar',
            'tipe_harga' => 'pilihan',
            'hapus_gambar_khusus' => '1',
            'aktif' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $layanan->refresh();
        $this->assertNull($layanan->gambar);
        $this->assertEquals($this->game->gambar_url, $layanan->gambar_url);
    }
}
