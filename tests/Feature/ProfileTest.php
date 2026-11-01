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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pengujian Halaman dan Fitur Profil Akun EiraStore:
 * - Hak akses halaman profil (guest redirect, customer/worker/admin dapat akses)
 * - Tautan navigasi kontekstual sesuai role pengguna
 * - Pembaruan data identitas (nama, nomor kontak WA, email)
 * - Pembaruan kata sandi akun
 * - Penghapusan akun pengguna secara mandiri
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_profile(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_profile_page_with_correct_elements(): void
    {
        $customer = User::factory()->create([
            'nama' => 'Budi Customer',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Pengaturan Akun');
        $response->assertSee('Budi Customer');
        $response->assertSee('budi@example.com');
        $response->assertSee('081234567890');
        $response->assertSee(route('pesanan.saya'));
    }

    public function test_admin_can_view_profile_page_with_link_to_admin_panel(): void
    {
        $admin = User::factory()->create([
            'nama' => 'Admin Eira',
            'email' => 'admin@eirastore.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Kembali ke Panel Admin');
        $response->assertSee(route('admin.dashboard'));
    }

    public function test_worker_can_view_profile_page_with_link_to_worker_orders(): void
    {
        $worker = User::factory()->create([
            'nama' => 'Worker Pro',
            'email' => 'worker@eirastore.com',
            'role' => 'worker',
        ]);

        $response = $this->actingAs($worker)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Pesanan Saya (Worker)');
        $response->assertSee(route('worker.pesanan'));
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'nama' => 'User Lama',
            'email' => 'lama@example.com',
            'no_hp' => '081234567890',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'nama' => 'User Baru',
            'email' => 'baru@example.com',
            'no_hp' => '081299998888',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('User Baru', $user->nama);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertSame('081299998888', $user->no_hp);
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password-lama-123'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password-lama-123',
            'password' => 'password-baru-456',
            'password_confirmation' => 'password-baru-456',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('password-baru-456', $user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password-asli-123'),
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
            'current_password' => 'salah-password',
            'password' => 'password-baru-456',
            'password_confirmation' => 'password-baru-456',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
        $user->refresh();
        $this->assertTrue(Hash::check('password-asli-123', $user->password));
    }

    public function test_user_can_delete_their_account_with_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password-rahasia'),
        ]);

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'password' => 'password-rahasia',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull(User::find($user->id));
    }

    public function test_user_cannot_delete_account_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password-rahasia'),
        ]);

        $response = $this->actingAs($user)->from(route('profile.edit'))->delete(route('profile.destroy'), [
            'password' => 'password-salah',
        ]);

        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull(User::find($user->id));
    }

    public function test_admin_cannot_delete_account(): void
    {
        $admin = User::factory()->create([
            'role' => Role::Admin,
            'password' => Hash::make('admin12345'),
        ]);

        $response = $this->actingAs($admin)->from(route('profile.edit'))->delete(route('profile.destroy'), [
            'password' => 'admin12345',
        ]);

        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull(User::find($admin->id));
    }

    public function test_worker_cannot_delete_account(): void
    {
        $worker = User::factory()->create([
            'role' => Role::Worker,
            'password' => Hash::make('worker12345'),
        ]);

        $response = $this->actingAs($worker)->from(route('profile.edit'))->delete(route('profile.destroy'), [
            'password' => 'worker12345',
        ]);

        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull(User::find($worker->id));
    }

    public function test_customer_with_order_history_cannot_delete_account(): void
    {
        $customer = User::factory()->create([
            'role' => Role::Customer,
            'password' => Hash::make('customer123'),
        ]);

        $game = Game::create([
            'nama_game' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'gambar' => 'games/ml.jpg',
            'aktif' => true,
        ]);

        $layanan = Layanan::create([
            'game_id' => $game->id,
            'nama_layanan' => 'Joki Rank',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);

        Pesanan::create([
            'kode_pesanan' => Pesanan::generateKodePesanan(),
            'customer_id' => $customer->id,
            'layanan_id' => $layanan->id,
            'id_akun_game' => '123456',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user@ml.com',
            'password_akun_game' => 'pass123',
            'total_harga' => 50000,
            'status' => StatusPesanan::Selesai,
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        $response = $this->actingAs($customer)->from(route('profile.edit'))->delete(route('profile.destroy'), [
            'password' => 'customer123',
        ]);

        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull(User::find($customer->id));
    }

    public function test_phone_number_in_profile_update_is_sanitized(): void
    {
        $customer = User::factory()->create([
            'nama' => 'Budi',
            'email' => 'budi@test.com',
            'no_hp' => '081234567890',
        ]);

        $response = $this->actingAs($customer)->patch(route('profile.update'), [
            'nama' => 'Budi Baru',
            'email' => 'budi@test.com',
            'no_hp' => '+62 812-9876-5432',
        ]);

        $response->assertSessionHasNoErrors();
        $customer->refresh();
        $this->assertSame('081298765432', $customer->no_hp);
    }

    public function test_delete_account_section_is_hidden_for_admin_and_worker(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $worker = User::factory()->create(['role' => Role::Worker]);
        $customer = User::factory()->create(['role' => Role::Customer]);

        $resAdmin = $this->actingAs($admin)->get(route('profile.edit'));
        $resAdmin->assertDontSee('Hapus Akun Saya');

        $resWorker = $this->actingAs($worker)->get(route('profile.edit'));
        $resWorker->assertDontSee('Hapus Akun Saya');

        $resCustomer = $this->actingAs($customer)->get(route('profile.edit'));
        $resCustomer->assertSee('Hapus Akun Saya');
    }
}
