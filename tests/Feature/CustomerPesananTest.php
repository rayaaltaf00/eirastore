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
            'no_wa_customer' => '081234567890',
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
        $this->assertEquals('081234567890', $draft['no_wa_customer']);
    }

    public function test_authenticated_customer_with_draft_is_redirected_to_confirmation(): void
    {
        $response = $this->withSession([
            'draft_pesanan' => [
                'layanan_id' => $this->layananTangga->id,
                'id_akun_game' => '12345678',
                'no_wa_customer' => '081234567890',
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
            'no_wa_customer' => '081234567890',
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
            'no_wa_customer' => '081234567890',
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
            'no_wa_customer' => '081234567890',
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
            'no_wa_customer' => '081234567890',
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
            'no_wa_customer' => '081234567890',
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
            'no_wa_customer' => '081234567890',
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

    public function test_gendong_mabar_service_does_not_require_password_or_login_via(): void
    {
        $layananGendong = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Gendong MLBB',
            'slug' => 'joki-gendong-mlbb',
            'tipe_harga' => TipeHarga::Pilihan,
            'butuh_login_game' => false,
            'estimasi_waktu' => '1 - 3 Jam',
            'aktif' => true,
            'urutan' => 2,
        ]);

        $itemPaket = LayananItem::create([
            'layanan_id' => $layananGendong->id,
            'nama' => 'Paket Mabar 3 Match',
            'jenis' => 'utama',
            'harga' => 30000,
            'aktif' => true,
            'urutan' => 1,
        ]);

        $payload = [
            'id_akun_game' => '87654321',
            'zone_id' => '2024',
            'nickname_game' => 'MabarPro',
            'no_wa_customer' => '081298765432',
            'items' => [
                $itemPaket->id => 1,
            ],
            // Tanpa login_via, username_akun_game, password_akun_game
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.store', $layananGendong), $payload);

        $pesanan = Pesanan::where('id_akun_game', '87654321')->first();
        $this->assertNotNull($pesanan);
        $this->assertNull($pesanan->password_akun_game);
        $this->assertNull($pesanan->username_akun_game);
        $this->assertEquals('081298765432', $pesanan->no_wa_customer);
        $this->assertEquals('MabarPro', $pesanan->nickname_game);
        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
    }

    public function test_invalid_whatsapp_number_fails_validation(): void
    {
        $payload = [
            'id_akun_game' => '12345678',
            'no_wa_customer' => '12345', // bukan format nomor Indonesia
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer@example.com',
            'password_akun_game' => 'SecretPass123!',
            'tier_awal_id' => $this->tierEpic->id,
            'bintang_awal' => 0,
            'tier_tujuan_id' => $this->tierLegend->id,
            'bintang_tujuan' => 1,
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.store', $this->layananTangga), $payload);

        $response->assertSessionHasErrors('no_wa_customer');
    }

    public function test_pilihan_order_form_displays_categories_tabs(): void
    {
        $layananQuest = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Quest & Storyline',
            'slug' => 'joki-quest-storyline',
            'tipe_harga' => TipeHarga::Pilihan,
            'butuh_login_game' => true,
            'estimasi_waktu' => '1 - 3 Hari',
            'aktif' => true,
            'urutan' => 3,
        ]);

        LayananItem::create([
            'layanan_id' => $layananQuest->id,
            'nama' => 'Chapter I: Farewell, Archaic Lord',
            'kategori' => 'Archon Quest',
            'jenis' => 'utama',
            'harga' => 45000,
            'urutan' => 1,
            'aktif' => true,
        ]);

        LayananItem::create([
            'layanan_id' => $layananQuest->id,
            'nama' => 'Tatara Tales (Full 7 Hari)',
            'kategori' => 'World Quest',
            'jenis' => 'utama',
            'harga' => 35000,
            'urutan' => 2,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('pesanan.create', $layananQuest));

        $response->assertOk();
        $response->assertViewHas('categories');
        $response->assertSee('Archon Quest');
        $response->assertSee('World Quest');
        $response->assertSee('Chapter I: Farewell, Archaic Lord');
        $response->assertSee('Tatara Tales (Full 7 Hari)');
    }

    public function test_customer_can_order_quest_items_across_different_categories(): void
    {
        $layananQuest = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Quest & Storyline',
            'slug' => 'joki-quest-storyline',
            'tipe_harga' => TipeHarga::Pilihan,
            'butuh_login_game' => true,
            'estimasi_waktu' => '1 - 3 Hari',
            'aktif' => true,
            'urutan' => 3,
        ]);

        $archonItem = LayananItem::create([
            'layanan_id' => $layananQuest->id,
            'nama' => 'Chapter I: Farewell, Archaic Lord',
            'kategori' => 'Archon Quest',
            'jenis' => 'utama',
            'harga' => 45000,
            'urutan' => 1,
            'aktif' => true,
        ]);

        $worldItem = LayananItem::create([
            'layanan_id' => $layananQuest->id,
            'nama' => 'The Chasm Delvers',
            'kategori' => 'World Quest',
            'jenis' => 'utama',
            'harga' => 35000,
            'urutan' => 2,
            'aktif' => true,
        ]);

        $fastTrackTambahan = LayananItem::create([
            'layanan_id' => $layananQuest->id,
            'nama' => 'Fast-Track Priority (Selesai Express)',
            'jenis' => 'tambahan',
            'harga' => 20000,
            'urutan' => 1,
            'aktif' => true,
        ]);

        $payload = [
            'id_akun_game' => '700123456',
            'server_game' => 'Asia',
            'nickname_game' => 'Traveler',
            'no_wa_customer' => '081234567890',
            'login_via' => 'HoYoverse',
            'username_akun_game' => 'traveler@genshin.com',
            'password_akun_game' => 'P@sswordGenshin123',
            'items' => [
                $archonItem->id => 1,
                $worldItem->id => 1,
            ],
            'item_tambahan_ids' => [
                $fastTrackTambahan->id,
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.store', $layananQuest), $payload);

        $expectedTotal = 45000 + 35000 + 20000; // 100.000

        $pesanan = Pesanan::where('id_akun_game', '700123456')->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals($expectedTotal, $pesanan->total_harga);
        $this->assertEquals(StatusPesanan::MenungguPembayaran, $pesanan->status);

        // Periksa item pesanan tersimpan
        $this->assertCount(3, $pesanan->pesananItems);
        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
    }

    public function test_pesanan_show_renders_successfully_for_all_order_statuses(): void
    {
        foreach (StatusPesanan::cases() as $status) {
            $pesanan = Pesanan::create([
                'kode_pesanan' => 'ES-'.now()->format('Ymd').'-'.substr(md5($status->value), 0, 5),
                'customer_id' => $this->customer->id,
                'layanan_id' => $this->layananTangga->id,
                'id_akun_game' => '12345678',
                'no_wa_customer' => '081234567890',
                'login_via' => 'Moonton',
                'username_akun_game' => 'gamer@example.com',
                'password_akun_game' => 'secret',
                'total_harga' => 39000,
                'status' => $status,
                'batas_waktu_bayar' => now()->addHours(2),
            ]);

            $response = $this->actingAs($this->customer)
                ->get(route('pesanan.show', $pesanan->kode_pesanan));

            $response->assertOk();
            $response->assertSee($pesanan->kode_pesanan);
            $response->assertSee($status->label());
        }
    }

    public function test_customer_can_order_progres_type_service_like_exploration_map(): void
    {
        $layananMap = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Eksplorasi Map',
            'slug' => 'joki-eksplorasi-map',
            'tipe_harga' => TipeHarga::Progres,
            'butuh_login_game' => true,
            'estimasi_waktu' => '2 - 3 Hari',
            'aktif' => true,
            'urutan' => 4,
        ]);

        $itemFontaine = LayananItem::create([
            'layanan_id' => $layananMap->id,
            'nama' => 'Fontaine (Belleau Region)',
            'jenis' => 'utama',
            'harga' => 150000, // Rp 150.000 untuk 100%
            'nilai_maks' => 100,
            'urutan' => 1,
            'aktif' => true,
        ]);

        $payload = [
            'id_akun_game' => '711223344',
            'server_game' => 'Asia',
            'nickname_game' => 'Aether',
            'no_wa_customer' => '081299887766',
            'login_via' => 'HoYoverse',
            'username_akun_game' => 'aether@teyvat.com',
            'password_akun_game' => 'P@ssword123',
            'progres' => [
                $itemFontaine->id => [
                    'awal' => 20,
                    'tujuan' => 100,
                ],
            ],
            'progres_selected' => [
                (string) $itemFontaine->id,
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.store', $layananMap), $payload);

        // Delta: 100 - 20 = 80%. Harga: 80 * 1.500 = Rp 120.000
        $pesanan = Pesanan::where('id_akun_game', '711223344')->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals(120000, $pesanan->total_harga);
        $this->assertEquals('Asia', $pesanan->server_game);
        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));

        // Akses halaman show
        $showResponse = $this->actingAs($this->customer)
            ->get(route('pesanan.show', $pesanan->kode_pesanan));
        $showResponse->assertOk();
        $showResponse->assertSee('120.000');
        $showResponse->assertSee('Fontaine (Belleau Region)');
    }

    public function test_whatsapp_number_is_properly_sanitized_and_normalized(): void
    {
        $payload = [
            'id_akun_game' => '998877',
            'no_wa_customer' => '+62 812-3456-7890', // Format bercampur spasi, strip, dan +62
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer@example.com',
            'password_akun_game' => 'SecretPass123!',
            'tier_awal_id' => $this->tierEpic->id,
            'bintang_awal' => 0,
            'tier_tujuan_id' => $this->tierLegend->id,
            'bintang_tujuan' => 1,
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.store', $this->layananTangga), $payload);

        $pesanan = Pesanan::where('id_akun_game', '998877')->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals('081234567890', $pesanan->no_wa_customer);
    }

    public function test_user_can_update_profile_nama_and_phone(): void
    {
        $response = $this->actingAs($this->customer)
            ->patch(route('profile.update'), [
                'nama' => 'John Super Customer',
                'email' => $this->customer->email,
                'no_hp' => '081234567899',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $this->customer->refresh();
        $this->assertEquals('John Super Customer', $this->customer->nama);
        $this->assertEquals('John Super Customer', $this->customer->name);
        $this->assertEquals('081234567899', $this->customer->no_hp);
    }
}
