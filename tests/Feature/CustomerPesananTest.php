<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\LayananItem;
use App\Models\MetodePembayaranTujuan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPesananTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $otherCustomer;

    protected Game $game;

    protected Layanan $layananTangga;

    protected LayananItem $tierGrandmaster;

    protected LayananItem $tierEpic;

    protected LayananItem $tierLegend;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->customer = User::factory()->create([
            'role' => Role::Customer,
            'nama' => 'John Customer',
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => Role::Customer,
            'nama' => 'Jane Other',
        ]);

        $this->game = Game::create([
            'nama_game' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'gambar' => 'ml.jpg',
            'aktif' => true,
            'urutan' => 1,
        ]);

        $this->layananTangga = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank Bintang',
            'slug' => 'joki-rank-bintang',
            'tipe_harga' => TipeHarga::Tangga,
            'estimasi_waktu' => '1 - 2 Hari',
            'aktif' => true,
            'urutan' => 1,
        ]);

        $this->tierGrandmaster = LayananItem::create([
            'layanan_id' => $this->layananTangga->id,
            'nama' => 'Grandmaster',
            'jenis' => 'utama',
            'harga' => 4000,
            'nilai_maks' => 5,
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->tierEpic = LayananItem::create([
            'layanan_id' => $this->layananTangga->id,
            'nama' => 'Epic',
            'jenis' => 'utama',
            'harga' => 6000,
            'nilai_maks' => 5,
            'urutan' => 2,
            'aktif' => true,
        ]);

        $this->tierLegend = LayananItem::create([
            'layanan_id' => $this->layananTangga->id,
            'nama' => 'Legend',
            'jenis' => 'utama',
            'harga' => 9000,
            'nilai_maks' => 5,
            'urutan' => 3,
            'aktif' => true,
        ]);

        MetodePembayaranTujuan::create([
            'nama_metode' => 'BCA EiraStore',
            'kategori' => 'bank',
            'nomor_rekening' => '8881234567',
            'atas_nama' => 'PT Eira Mega Perkasa',
            'aktif' => true,
            'urutan' => 1,
        ]);
    }

    public function test_guest_can_view_order_form(): void
    {
        $response = $this->get(route('pesanan.create', $this->layananTangga));

        $response->assertStatus(200);
        $response->assertSee('Joki Rank Bintang');
        $response->assertSee('Data Akun Game');
    }

    public function test_guest_submitting_order_form_stores_draft_in_session_and_redirects_to_login(): void
    {
        $payload = [
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer@example.com',
            'password_akun_game' => 'SecretPass123!',
            'tier_awal_id' => $this->tierEpic->id,
            'bintang_awal' => 0,
            'tier_tujuan_id' => $this->tierLegend->id,
            'bintang_tujuan' => 1,
        ];

        $response = $this->post(route('pesanan.store', $this->layananTangga), $payload);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('draft_pesanan');
        $draft = session('draft_pesanan');
        $this->assertEquals($this->layananTangga->id, $draft['layanan_id']);
        $this->assertEquals('gamer@example.com', $draft['username_akun_game']);
    }

    public function test_authenticated_customer_with_draft_is_redirected_to_confirmation(): void
    {
        $response = $this->withSession([
            'draft_pesanan' => [
                'layanan_id' => $this->layananTangga->id,
                'id_akun_game' => '12345678',
                'login_via' => 'Moonton',
                'username_akun_game' => 'gamer@example.com',
                'password_akun_game' => 'SecretPass123!',
                'tier_awal_id' => $this->tierEpic->id,
                'bintang_awal' => 0,
                'tier_tujuan_id' => $this->tierLegend->id,
                'bintang_tujuan' => 1,
            ],
        ])->post(route('login'), [
            'email' => $this->customer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('pesanan.konfirmasi-draf'));
    }

    public function test_customer_can_confirm_draft_and_order_is_created_with_2_hour_expiration(): void
    {
        $draft = [
            'layanan_id' => $this->layananTangga->id,
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer@example.com',
            'password_akun_game' => 'SecretPass123!',
            'tier_awal_id' => $this->tierEpic->id,
            'bintang_awal' => 0,
            'tier_tujuan_id' => $this->tierLegend->id,
            'bintang_tujuan' => 1,
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['draft_pesanan' => $draft])
            ->post(route('pesanan.konfirmasi-draf.proses'));

        $this->assertDatabaseHas('pesanan', [
            'customer_id' => $this->customer->id,
            'id_akun_game' => '12345678',
            'status' => StatusPesanan::MenungguPembayaran->value,
            'total_harga' => 39000, // 5*6000 + 1*9000
        ]);

        $pesanan = Pesanan::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($pesanan);
        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));

        // Verifikasi batas waktu 2 jam ke depan (margin 2 menit)
        $diffMinutes = now()->diffInMinutes($pesanan->batas_waktu_bayar, false);
        $this->assertGreaterThanOrEqual(118, $diffMinutes);
        $this->assertLessThanOrEqual(121, $diffMinutes);

        // Verifikasi password akun game terenkripsi secara aman
        $this->assertEquals('SecretPass123!', $pesanan->password_akun_game);
        $response->assertSessionMissing('draft_pesanan');
    }

    public function test_antispam_prevents_creating_new_order_while_having_active_unpaid_order(): void
    {
        // Buat pesanan pertama yang masih aktif menunggu pembayaran
        $pesananPertama = Pesanan::create([
            'kode_pesanan' => 'ES-20261025-001',
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layananTangga->id,
            'id_akun_game' => '11111111',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user1@example.com',
            'password_akun_game' => 'pass1',
            'total_harga' => 39000,
            'status' => StatusPesanan::MenungguPembayaran,
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        // Percobaan membuat pesanan kedua
        $payloadKedua = [
            'id_akun_game' => '22222222',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user2@example.com',
            'password_akun_game' => 'pass2',
            'tier_awal_id' => $this->tierEpic->id,
            'bintang_awal' => 0,
            'tier_tujuan_id' => $this->tierLegend->id,
            'bintang_tujuan' => 1,
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.store', $this->layananTangga), $payloadKedua);

        // Harus diblokir dan diredirect kembali ke pesanan pertama
        $response->assertRedirect(route('pesanan.show', $pesananPertama->kode_pesanan));
        $response->assertSessionHas('error');

        // Pastikan pesanan kedua TIDAK pernah masuk ke database
        $this->assertDatabaseMissing('pesanan', [
            'id_akun_game' => '22222222',
        ]);
    }

    public function test_customer_can_cancel_unpaid_order_and_credentials_are_wiped_immediately(): void
    {
        $pesanan = Pesanan::create([
            'kode_pesanan' => 'ES-20261025-002',
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layananTangga->id,
            'id_akun_game' => '11111111',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user1@example.com',
            'password_akun_game' => 'pass1',
            'total_harga' => 39000,
            'status' => StatusPesanan::MenungguPembayaran,
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.batalkan', $pesanan->kode_pesanan));

        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::Dibatalkan, $pesanan->status);
        $this->assertNull($pesanan->password_akun_game);
        $this->assertNotNull($pesanan->kredensial_dihapus_at);

        // Setelah dibatalkan, customer bebas membuat pesanan baru
        $payloadBaru = [
            'id_akun_game' => '33333333',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user3@example.com',
            'password_akun_game' => 'pass3',
            'tier_awal_id' => $this->tierEpic->id,
            'bintang_awal' => 0,
            'tier_tujuan_id' => $this->tierLegend->id,
            'bintang_tujuan' => 1,
        ];

        $responseBaru = $this->actingAs($this->customer)
            ->post(route('pesanan.store', $this->layananTangga), $payloadBaru);

        $this->assertDatabaseHas('pesanan', [
            'id_akun_game' => '33333333',
            'status' => StatusPesanan::MenungguPembayaran->value,
        ]);
    }

    public function test_customer_can_upload_payment_proof(): void
    {
        $pesanan = Pesanan::create([
            'kode_pesanan' => 'ES-20261025-003',
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layananTangga->id,
            'id_akun_game' => '11111111',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user1@example.com',
            'password_akun_game' => 'pass1',
            'total_harga' => 39000,
            'status' => StatusPesanan::MenungguPembayaran,
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        $file = UploadedFile::fake()->image('struk_bca.jpg', 600, 800);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.bukti.unggah', $pesanan->kode_pesanan), [
                'metode_pembayaran' => 'BCA EiraStore - 8881234567',
                'bukti_transfer' => $file,
            ]);

        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::MenungguVerifikasi, $pesanan->status);

        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'BCA EiraStore - 8881234567',
            'status' => StatusPembayaran::MenungguVerifikasi->value,
        ]);

        $path = $pesanan->pembayaran->bukti_transfer;
        Storage::disk('local')->assertExists($path);
    }

    public function test_expired_order_cannot_receive_payment_proof_and_auto_wipes_password_on_access(): void
    {
        // Pesanan dibuat 3 jam lalu (sudah lewat 2 jam)
        $pesanan = Pesanan::create([
            'kode_pesanan' => 'ES-20261025-004',
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layananTangga->id,
            'id_akun_game' => '11111111',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user1@example.com',
            'password_akun_game' => 'pass1',
            'total_harga' => 39000,
            'status' => StatusPesanan::MenungguPembayaran,
            'batas_waktu_bayar' => now()->subHour(),
        ]);

        // Customer buka halaman show -> JIT check langsung ubah status ke kedaluwarsa & hapus password
        $response = $this->actingAs($this->customer)
            ->get(route('pesanan.show', $pesanan->kode_pesanan));

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::Kedaluwarsa, $pesanan->status);
        $this->assertNull($pesanan->password_akun_game);
        $this->assertNotNull($pesanan->kredensial_dihapus_at);

        // Upload bukti pembayaran harus ditolak
        $file = UploadedFile::fake()->image('struk_terlambat.jpg');
        $uploadResponse = $this->actingAs($this->customer)
            ->post(route('pesanan.bukti.unggah', $pesanan->kode_pesanan), [
                'metode_pembayaran' => 'BCA EiraStore',
                'bukti_transfer' => $file,
            ]);

        $uploadResponse->assertSessionHas('error');
    }

    public function test_customer_cannot_view_or_cancel_other_customer_orders(): void
    {
        $pesanan = Pesanan::create([
            'kode_pesanan' => 'ES-20261025-005',
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layananTangga->id,
            'id_akun_game' => '11111111',
            'login_via' => 'Moonton',
            'username_akun_game' => 'user1@example.com',
            'password_akun_game' => 'pass1',
            'total_harga' => 39000,
            'status' => StatusPesanan::MenungguPembayaran,
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        // Customer lain mencoba mengakses show
        $response = $this->actingAs($this->otherCustomer)
            ->get(route('pesanan.show', $pesanan->kode_pesanan));

        $response->assertStatus(403);

        // Customer lain mencoba membatalkan
        $cancelResponse = $this->actingAs($this->otherCustomer)
            ->post(route('pesanan.batalkan', $pesanan->kode_pesanan));

        $cancelResponse->assertStatus(403);
    }
}
