<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\MetodePembayaranTujuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMetodePembayaranTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
        ]);

        $this->customer = User::factory()->create([
            'role' => Role::Customer,
        ]);
    }

    public function test_guest_cannot_access_metode_pembayaran(): void
    {
        $response = $this->get(route('admin.metode-pembayaran.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_metode_pembayaran(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.metode-pembayaran.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_view_metode_pembayaran_list(): void
    {
        MetodePembayaranTujuan::create([
            'nama_metode' => 'Bank BCA',
            'kategori' => 'bank',
            'nomor_rekening' => '1234567890',
            'atas_nama' => 'Eira Store',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.metode-pembayaran.index'));
        $response->assertOk();
        $response->assertSee('Bank BCA');
        $response->assertSee('1234567890');
    }

    public function test_admin_can_create_metode_pembayaran(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.metode-pembayaran.store'), [
            'nama_metode' => 'DANA E-Wallet',
            'kategori' => 'ewallet',
            'nomor_rekening' => '081234567890',
            'atas_nama' => 'Eira Store Dana',
            'petunjuk_transfer' => 'Transfer DANA ke nomor ini',
            'urutan' => 1,
            'aktif' => 1,
        ]);

        $response->assertRedirect(route('admin.metode-pembayaran.index'));
        $this->assertDatabaseHas('metode_pembayaran_tujuan', [
            'nama_metode' => 'DANA E-Wallet',
            'nomor_rekening' => '081234567890',
        ]);
    }

    public function test_admin_can_update_metode_pembayaran(): void
    {
        $metode = MetodePembayaranTujuan::create([
            'nama_metode' => 'Bank Mandiri',
            'kategori' => 'bank',
            'nomor_rekening' => '111222333',
            'atas_nama' => 'Eira Store',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.metode-pembayaran.update', $metode), [
            'nama_metode' => 'Bank Mandiri Updated',
            'kategori' => 'bank',
            'nomor_rekening' => '999888777',
            'atas_nama' => 'Eira Store Updated',
            'urutan' => 2,
            'aktif' => 1,
        ]);

        $response->assertRedirect(route('admin.metode-pembayaran.index'));
        $this->assertDatabaseHas('metode_pembayaran_tujuan', [
            'id' => $metode->id,
            'nama_metode' => 'Bank Mandiri Updated',
            'nomor_rekening' => '999888777',
        ]);
    }

    public function test_admin_can_delete_metode_pembayaran(): void
    {
        $metode = MetodePembayaranTujuan::create([
            'nama_metode' => 'GoPay',
            'kategori' => 'ewallet',
            'nomor_rekening' => '0899999999',
            'atas_nama' => 'Eira Store',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.metode-pembayaran.destroy', $metode));
        $response->assertRedirect(route('admin.metode-pembayaran.index'));
        $this->assertDatabaseMissing('metode_pembayaran_tujuan', [
            'id' => $metode->id,
        ]);
    }
}
