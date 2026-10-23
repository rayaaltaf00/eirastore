<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian middleware role:
 * - Guest tidak bisa akses /admin/dashboard & /worker/pesanan (redirect ke /login)
 * - Customer tidak bisa akses /admin/dashboard (403)
 * - Customer tidak bisa akses /worker/pesanan (403)
 * - Admin bisa akses /admin/dashboard (200)
 * - Worker bisa akses /worker/pesanan (200)
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_protected_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/worker/pesanan')->assertRedirect('/login');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_worker_pesanan(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/worker/pesanan');

        $response->assertStatus(403);
    }

    public function test_worker_cannot_access_admin_dashboard(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $response = $this->actingAs($worker)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_worker_can_access_worker_pesanan(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $response = $this->actingAs($worker)->get('/worker/pesanan');

        $response->assertStatus(200);
    }
}
