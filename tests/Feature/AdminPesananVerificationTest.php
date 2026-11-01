<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPesananVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $worker;

    private User $customer;

    private Layanan $layanan;

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
            'nama' => 'Worker MLBB Eira',
            'status_akun' => 'aktif',
            'no_hp' => '081234567890',
        ]);

        $this->customer = User::factory()->create([
            'role' => Role::Customer,
            'nama' => 'Pelanggan Setia',
            'status_akun' => 'aktif',
        ]);

        $game = Game::create([
            'nama_game' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'gambar' => 'games/ml.jpg',
            'aktif' => true,
        ]);

        $this->layanan = Layanan::create([
            'game_id' => $game->id,
            'nama_layanan' => 'Joki Rank Mythic',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);
    }

    private function buatPesanan(array $attributes = []): Pesanan
    {
        return Pesanan::create(array_merge([
            'kode_pesanan' => Pesanan::generateKodePesanan(),
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layanan->id,
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer_ml@gmail.com',
            'password_akun_game' => 'SecretPass123!',
            'nickname_game' => 'ProPlayer',
            'server_game' => 'Asia',
            'total_harga' => 150000,
            'status' => StatusPesanan::MenungguVerifikasi,
            'data_disetujui' => false,
            'batas_waktu_bayar' => now()->addHours(2),
        ], $attributes));
    }

    // ── Test A-19: Dashboard Metrik Pesanan ──────────────

    public function test_admin_can_view_dashboard_with_all_metrics(): void
    {
        $this->buatPesanan(['status' => StatusPesanan::MenungguVerifikasi]);
        $this->buatPesanan(['status' => StatusPesanan::SiapDitugaskan]);
        $this->buatPesanan(['status' => StatusPesanan::Selesai, 'total_harga' => 200000]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Distribusi Status Pesanan (11 Status Kunci)');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Siap Ditugaskan');
        $response->assertSee('Selesai (Tuntas)');
        $response->assertSee('200.000'); // Omzet dari pesanan selesai
    }

    // ── Test A-20: Daftar Pesanan & Filter ───────────────

    public function test_admin_can_view_order_list_with_actionable_default_filter(): void
    {
        $orderActionable = $this->buatPesanan(['status' => StatusPesanan::MenungguVerifikasi]);
        $orderDone = $this->buatPesanan(['status' => StatusPesanan::Selesai]);

        // Default view: menyaring actionable orders
        $response = $this->actingAs($this->admin)->get(route('admin.pesanan.index'));
        $response->assertStatus(200);
        $response->assertSee($orderActionable->kode_pesanan);
        $response->assertDontSee($orderDone->kode_pesanan);

        // Filter status=semua: melihat semua pesanan
        $responseAll = $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['status' => 'semua']));
        $responseAll->assertStatus(200);
        $responseAll->assertSee($orderActionable->kode_pesanan);
        $responseAll->assertSee($orderDone->kode_pesanan);
    }

    public function test_admin_can_view_order_detail_without_seeing_game_password(): void
    {
        $order = $this->buatPesanan([
            'password_akun_game' => 'SUPER_CONFIDENTIAL_PASS_999',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pesanan.show', $order->kode_pesanan));

        $response->assertStatus(200);
        $response->assertSee($order->kode_pesanan);
        $response->assertSee($order->id_akun_game);
        $response->assertSee($order->username_akun_game);
        // PRD Mandate: Password must NOT be visible to Admin in plain text
        $response->assertDontSee('SUPER_CONFIDENTIAL_PASS_999');
        $response->assertSee('Hanya Didekripsi untuk Worker');
    }

    // ── Test A-21: Setujui Data Akun ─────────────────────

    public function test_admin_can_approve_order_data_when_payment_not_yet_approved(): void
    {
        $order = $this->buatPesanan([
            'status' => StatusPesanan::MenungguVerifikasi,
            'data_disetujui' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.setujui-data', $order->kode_pesanan));

        $response->assertRedirect();
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'status' => StatusPesanan::MenungguVerifikasi->value,
            'data_disetujui' => true,
        ]);
    }

    public function test_approving_order_data_transitions_to_siap_ditugaskan_if_payment_already_approved(): void
    {
        $order = $this->buatPesanan([
            'status' => StatusPesanan::MenungguVerifikasi,
            'data_disetujui' => false,
        ]);

        Pembayaran::create([
            'pesanan_id' => $order->id,
            'metode_pembayaran' => 'BCA',
            'bukti_transfer' => 'bukti_pembayaran/dummy.jpg',
            'status' => StatusPembayaran::Disetujui,
            'diverifikasi_oleh' => $this->admin->id,
            'diverifikasi_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.setujui-data', $order->kode_pesanan));

        $response->assertRedirect();
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'status' => StatusPesanan::SiapDitugaskan->value,
            'data_disetujui' => true,
        ]);
    }

    // ── Test A-22: Minta Revisi Data ─────────────────────

    public function test_admin_can_request_data_revision_with_valid_note(): void
    {
        $order = $this->buatPesanan([
            'status' => StatusPesanan::MenungguVerifikasi,
            'data_disetujui' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.minta-revisi-data', $order->kode_pesanan), [
                'catatan' => 'Mohon sertakan Zone ID pada format ID game akun MLBB Anda.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'status' => StatusPesanan::PerluRevisiData->value,
            'data_disetujui' => false,
        ]);
    }

    public function test_admin_cannot_request_data_revision_with_empty_or_too_short_note(): void
    {
        $order = $this->buatPesanan(['status' => StatusPesanan::MenungguVerifikasi]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.minta-revisi-data', $order->kode_pesanan), [
                'catatan' => '123', // less than 5 characters
            ]);

        $response->assertSessionHasErrors('catatan');
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'status' => StatusPesanan::MenungguVerifikasi->value,
        ]);
    }

    // ── Test C-10: Customer Revisi Data Akun ──────────────

    public function test_customer_can_revise_game_account_data(): void
    {
        $order = $this->buatPesanan([
            'status' => StatusPesanan::PerluRevisiData,
            'id_akun_game' => '12345',
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.revisi-data', $order->kode_pesanan), [
                'id_akun_game' => '99887766',
                'login_via' => 'Moonton',
                'zone_id' => '2123',
                'catatan_customer' => 'Sudah saya perbaiki dengan Zone ID.',
            ]);

        $response->assertRedirect(route('pesanan.show', $order->kode_pesanan));
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'id_akun_game' => '99887766',
            'status' => StatusPesanan::MenungguVerifikasi->value,
            'data_disetujui' => false,
        ]);
    }

    // ── Test A-23: Setujui Bukti Transfer ────────────────

    public function test_admin_can_approve_payment_proof(): void
    {
        $order = $this->buatPesanan([
            'status' => StatusPesanan::MenungguVerifikasi,
            'data_disetujui' => true,
        ]);

        $pembayaran = Pembayaran::create([
            'pesanan_id' => $order->id,
            'metode_pembayaran' => 'BCA',
            'bukti_transfer' => 'bukti_pembayaran/dummy.jpg',
            'status' => StatusPembayaran::MenungguVerifikasi,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.setujui-pembayaran', $order->kode_pesanan));

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayaran', [
            'id' => $pembayaran->id,
            'status' => StatusPembayaran::Disetujui->value,
            'diverifikasi_oleh' => $this->admin->id,
        ]);
        // Since data was already approved, status moves to SiapDitugaskan
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'status' => StatusPesanan::SiapDitugaskan->value,
        ]);
    }

    // ── Test A-24: Tolak Bukti Transfer ──────────────────

    public function test_admin_can_reject_payment_proof_and_password_is_wiped(): void
    {
        $order = $this->buatPesanan([
            'status' => StatusPesanan::MenungguVerifikasi,
            'password_akun_game' => 'MySecretPass123',
        ]);

        $pembayaran = Pembayaran::create([
            'pesanan_id' => $order->id,
            'metode_pembayaran' => 'BCA',
            'bukti_transfer' => 'bukti_pembayaran/dummy.jpg',
            'status' => StatusPembayaran::MenungguVerifikasi,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.tolak-pembayaran', $order->kode_pesanan), [
                'alasan' => 'Struk bukti transfer terindikasi palsu / nomor referensi tidak terdaftar.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pembayaran', [
            'id' => $pembayaran->id,
            'status' => StatusPembayaran::Ditolak->value,
        ]);

        $order->refresh();
        $this->assertEquals(StatusPesanan::Dibatalkan, $order->status);
        // Security check: password is wiped to NULL
        $this->assertNull($order->password_akun_game);
        $this->assertNotNull($order->kredensial_dihapus_at);
    }

    // ── Test A-25: Penugasan Pesanan ke Worker ────────────

    public function test_admin_can_assign_ready_order_to_active_worker(): void
    {
        $order = $this->buatPesanan([
            'status' => StatusPesanan::SiapDitugaskan,
            'data_disetujui' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.tugaskan-worker', $order->kode_pesanan), [
                'worker_id' => $this->worker->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'worker_id' => $this->worker->id,
            'status' => StatusPesanan::Diproses->value,
        ]);
    }

    public function test_admin_cannot_assign_order_to_inactive_worker(): void
    {
        $inactiveWorker = User::factory()->create([
            'role' => Role::Worker,
            'status_akun' => 'nonaktif',
        ]);

        $order = $this->buatPesanan(['status' => StatusPesanan::SiapDitugaskan]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pesanan.tugaskan-worker', $order->kode_pesanan), [
                'worker_id' => $inactiveWorker->id,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('pesanan', [
            'id' => $order->id,
            'worker_id' => null,
            'status' => StatusPesanan::SiapDitugaskan->value,
        ]);
    }
}
