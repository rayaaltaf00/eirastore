<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontAndLayoutTest extends TestCase
{
    use RefreshDatabase;

    /** Tes halaman beranda publik dapat diakses dengan sukses */
    public function test_halaman_beranda_dapat_diakses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('EIRASTORE');
        $response->assertSee('Layanan Joki');
        $response->assertSee('navigateToSection');
        $response->assertSee('navigateToHome');
        $response->assertSee('#katalog-game');
        $response->assertSee('#cara-pesan');
        $response->assertSee('#keunggulan');
    }

    /** Tes halaman beranda menangani pencarian game dengan aman */
    public function test_halaman_beranda_menangani_parameter_pencarian(): void
    {
        $response = $this->get('/?q=Valorant');

        $response->assertStatus(200);
        $response->assertSee('Valorant');
    }

    /** Tes halaman pelacakan pesanan dapat diakses */
    public function test_halaman_lacak_pesanan_dapat_diakses(): void
    {
        $response = $this->get('/lacak');

        $response->assertStatus(200);
        $response->assertSee('Lacak Pesanan Joki');
    }

    /** Tes customer login dapat mengakses riwayat pesanan saya */
    public function test_customer_dapat_mengakses_pesanan_saya(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($customer)->get('/pesanan-saya');

        $response->assertStatus(200);
        $response->assertSee('Pesanan Saya');
    }

    /** Tes user nonaktif langsung dikeluarkan oleh middleware EnsureUserIsActive */
    public function test_user_nonaktif_dipaksa_logout_saat_akses_aplikasi(): void
    {
        $user = User::factory()->create([
            'status_akun' => 'nonaktif',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /** Tes admin dapat mengakses admin dashboard */
    public function test_admin_dapat_mengakses_dashboard_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Utama');
    }

    /** Tes worker dapat mengakses halaman pesanan worker */
    public function test_worker_dapat_mengakses_pesanan_worker(): void
    {
        $worker = User::factory()->create([
            'role' => 'worker',
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($worker)->get('/worker/pesanan');

        $response->assertStatus(200);
        $response->assertSee('Pesanan Ditugaskan');
    }

    /** Tes halaman lacak pesanan jika kode tidak ditemukan */
    public function test_halaman_lacak_pesanan_menampilkan_pesan_error_jika_tidak_ditemukan(): void
    {
        $response = $this->get('/lacak?invoice=ES-99999999-999');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** Tes artisan command alias pesanan:kedaluwarsakan sesuai PRD 3.7 */
    public function test_artisan_command_alias_pesanan_kedaluwarsakan_berhasil_dijalankan(): void
    {
        $this->artisan('pesanan:kedaluwarsakan')
            ->assertSuccessful();
    }
}
