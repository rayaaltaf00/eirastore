<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusPesanan;
use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $worker;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'nama' => 'Admin EiraStore',
            'status_akun' => 'aktif',
        ]);

        $this->worker = User::factory()->create([
            'role' => Role::Worker,
            'nama' => 'Worker Pro MLBB',
            'email' => 'worker1@eirastore.id',
            'no_hp' => '081234567890',
            'status_akun' => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'role' => Role::Customer,
            'nama' => 'Customer Gamers',
            'status_akun' => 'aktif',
        ]);
    }

    public function test_guest_cannot_access_worker_management(): void
    {
        $this->get(route('admin.workers.index'))->assertRedirect(route('login'));
        $this->get(route('admin.workers.create'))->assertRedirect(route('login'));
        $this->get(route('admin.workers.edit', $this->worker))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_worker_management(): void
    {
        // Customer mendapat 403 Forbidden
        $this->actingAs($this->customer)
            ->get(route('admin.workers.index'))
            ->assertStatus(403);

        // Worker sendiri tidak boleh mengakses CRUD manajemen worker admin
        $this->actingAs($this->worker)
            ->get(route('admin.workers.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_view_workers_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.workers.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Tim Worker');
        $response->assertSee('Worker Pro MLBB');
        $response->assertSee('worker1@eirastore.id');
        $response->assertSee('081234567890');
    }

    public function test_admin_can_filter_and_search_workers(): void
    {
        $workerNonaktif = User::factory()->create([
            'role' => Role::Worker,
            'nama' => 'Worker Cuti',
            'email' => 'cuti@eirastore.id',
            'status_akun' => 'nonaktif',
        ]);

        // Cari berdasarkan nama
        $resSearch = $this->actingAs($this->admin)
            ->get(route('admin.workers.index', ['q' => 'Cuti']));
        $resSearch->assertSee('Worker Cuti');
        $resSearch->assertDontSee('Worker Pro MLBB');

        // Filter status nonaktif
        $resStatus = $this->actingAs($this->admin)
            ->get(route('admin.workers.index', ['status' => 'nonaktif']));
        $resStatus->assertSee('Worker Cuti');
        $resStatus->assertDontSee('Worker Pro MLBB');
    }

    public function test_admin_can_view_create_worker_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.workers.create'));

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun Worker Baru');
        $response->assertSee('Ketentuan Akun Worker');
    }

    public function test_admin_can_store_new_worker_with_valid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.workers.store'), [
            'nama' => 'Joki Handal ML',
            'email' => 'joki.ml@eirastore.id',
            'no_hp' => '085712345678',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $response->assertRedirect(route('admin.workers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nama' => 'Joki Handal ML',
            'email' => 'joki.ml@eirastore.id',
            'no_hp' => '085712345678',
            'role' => 'worker',
            'status_akun' => 'aktif',
        ]);
    }

    public function test_admin_cannot_store_worker_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.workers.store'), [
            'nama' => 'Worker Kloning',
            'email' => 'worker1@eirastore.id', // email sudah dipakai $this->worker
            'no_hp' => '089912345678',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_cannot_store_worker_with_invalid_phone_or_short_password(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.workers.store'), [
            'nama' => 'Worker Tester',
            'email' => 'tester@eirastore.id',
            'no_hp' => 'bukan-angka',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors(['no_hp', 'password']);
    }

    public function test_admin_can_view_edit_worker_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.workers.edit', $this->worker));

        $response->assertStatus(200);
        $response->assertSee('Ubah Profil Worker: Worker Pro MLBB');
        $response->assertSee('worker1@eirastore.id');
    }

    public function test_admin_can_update_worker_details(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.workers.update', $this->worker), [
            'nama' => 'Worker Pro MLBB Edited',
            'email' => 'worker1.updated@eirastore.id',
            'no_hp' => '081298765432',
            'status_akun' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.workers.index'));
        $response->assertSessionHas('success');

        $this->worker->refresh();
        $this->assertEquals('Worker Pro MLBB Edited', $this->worker->nama);
        $this->assertEquals('worker1.updated@eirastore.id', $this->worker->email);
        $this->assertEquals('081298765432', $this->worker->no_hp);
    }

    public function test_admin_cannot_deactivate_worker_with_active_orders_via_update(): void
    {
        // Siapkan game, layanan, dan pesanan aktif untuk worker
        $game = Game::create([
            'nama_game' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'gambar' => 'ml.jpg',
            'aktif' => true,
        ]);

        $layanan = Layanan::create([
            'game_id' => $game->id,
            'nama_layanan' => 'Joki Rank Bintang',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        Pesanan::create([
            'kode_pesanan' => 'ES-20261026-001',
            'customer_id' => $this->customer->id,
            'worker_id' => $this->worker->id,
            'layanan_id' => $layanan->id,
            'id_akun_game' => '12345678',
            'no_wa_customer' => '081234567890',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamers@example.com',
            'password_akun_game' => 'rahasia123',
            'total_harga' => 50000,
            'status' => StatusPesanan::SedangDikerjakan, // Status aktif
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        // Coba ubah status_akun menjadi nonaktif
        $response = $this->actingAs($this->admin)->put(route('admin.workers.update', $this->worker), [
            'nama' => $this->worker->nama,
            'email' => $this->worker->email,
            'no_hp' => $this->worker->no_hp,
            'status_akun' => 'nonaktif',
        ]);

        $response->assertSessionHasErrors(['status_akun']);

        $this->worker->refresh();
        $this->assertEquals('aktif', $this->worker->status_akun);
    }

    public function test_admin_can_deactivate_worker_without_active_orders_via_update(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.workers.update', $this->worker), [
            'nama' => $this->worker->nama,
            'email' => $this->worker->email,
            'no_hp' => $this->worker->no_hp,
            'status_akun' => 'nonaktif',
        ]);

        $response->assertRedirect(route('admin.workers.index'));
        $response->assertSessionHas('success');

        $this->worker->refresh();
        $this->assertEquals('nonaktif', $this->worker->status_akun);
    }

    public function test_admin_cannot_toggle_worker_to_nonaktif_if_has_active_orders(): void
    {
        $game = Game::create([
            'nama_game' => 'Valorant',
            'slug' => 'valorant',
            'gambar' => 'val.jpg',
            'aktif' => true,
        ]);

        $layanan = Layanan::create([
            'game_id' => $game->id,
            'nama_layanan' => 'Joki Rank Radiant',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        Pesanan::create([
            'kode_pesanan' => 'ES-20261026-002',
            'customer_id' => $this->customer->id,
            'worker_id' => $this->worker->id,
            'layanan_id' => $layanan->id,
            'id_akun_game' => 'riot#1234',
            'no_wa_customer' => '081234567890',
            'login_via' => 'Riot',
            'username_akun_game' => 'riotuser',
            'password_akun_game' => 'riotpass',
            'total_harga' => 100000,
            'status' => StatusPesanan::Diproses, // Status aktif
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.workers.toggle-status', $this->worker));

        $response->assertSessionHas('error');

        $this->worker->refresh();
        $this->assertEquals('aktif', $this->worker->status_akun);
    }

    public function test_admin_can_toggle_worker_status_back_and_forth_when_no_active_orders(): void
    {
        // Toggle Aktif -> Nonaktif
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.workers.toggle-status', $this->worker));

        $response->assertSessionHas('success');
        $this->worker->refresh();
        $this->assertEquals('nonaktif', $this->worker->status_akun);

        // Toggle Nonaktif -> Aktif kembali
        $response2 = $this->actingAs($this->admin)
            ->patch(route('admin.workers.toggle-status', $this->worker));

        $response2->assertSessionHas('success');
        $this->worker->refresh();
        $this->assertEquals('aktif', $this->worker->status_akun);
    }

    public function test_cannot_edit_or_toggle_non_worker_user(): void
    {
        // $this->customer memiliki role Customer, bukan Worker
        $this->actingAs($this->admin)
            ->get(route('admin.workers.edit', $this->customer))
            ->assertStatus(404);

        $this->actingAs($this->admin)
            ->patch(route('admin.workers.toggle-status', $this->customer))
            ->assertStatus(404);
    }
}
