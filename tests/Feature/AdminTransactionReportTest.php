<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusPesanan;
use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminTransactionReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private User $worker;
    private Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'email' => 'admin@example.com',
        ]);

        $this->customer = User::factory()->create([
            'role' => Role::Customer,
            'nama' => 'Budi Gamer',
            'email' => 'customer@example.com',
        ]);

        $this->worker = User::factory()->create([
            'role' => Role::Worker,
            'nama' => 'Worker Pro',
            'email' => 'worker@example.com',
        ]);

        $game = Game::create([
            'nama_game' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'kategori' => 'MOBA',
            'gambar' => 'ml.png',
            'aktif' => true,
        ]);

        $this->layanan = Layanan::create([
            'game_id' => $game->id,
            'nama_layanan' => 'Rank Mythic',
            'tipe_harga' => TipeHarga::Pilihan,
            'harga' => 150000,
            'aktif' => true,
        ]);
    }

    private function buatPesananSelesai(int $totalHarga, Carbon $completedAt): Pesanan
    {
        $pesanan = Pesanan::create([
            'kode_pesanan' => 'ORD-' . strtoupper(uniqid()),
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layanan->id,
            'worker_id' => $this->worker->id,
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer_id',
            'nickname_game' => 'ProGamer',
            'server_game' => '1001',
            'status' => StatusPesanan::Selesai,
            'total_harga' => $totalHarga,
            'batas_waktu_bayar' => now()->subDays(5),
            'selesai_at' => $completedAt,
        ]);

        $pesanan->created_at = $completedAt;
        $pesanan->save();

        return $pesanan;
    }

    public function test_admin_dapat_mengakses_laporan_transaksi(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan-transaksi.index'));

        $response->assertOk();
        $response->assertViewIs('admin.laporan-transaksi.index');
        $response->assertSee('Laporan Transaksi');
    }

    public function test_non_admin_dan_guest_tidak_dapat_mengakses_laporan_transaksi(): void
    {
        // Guest dialihkan ke login
        $this->get(route('admin.laporan-transaksi.index'))
            ->assertRedirect(route('login'));

        // Customer dilarang (403)
        $this->actingAs($this->customer)
            ->get(route('admin.laporan-transaksi.index'))
            ->assertForbidden();

        // Worker dilarang (403)
        $this->actingAs($this->worker)
            ->get(route('admin.laporan-transaksi.index'))
            ->assertForbidden();
    }

    public function test_laporan_menghitung_total_transaksi_pendapatan_dan_rata_rata(): void
    {
        $now = Carbon::parse('2025-05-15 10:00:00');
        Carbon::setTestNow($now);

        // Buat 3 pesanan selesai dalam bulan berjalan
        $this->buatPesananSelesai(100000, $now->copy()->subDays(2));
        $this->buatPesananSelesai(200000, $now->copy()->subDays(1));
        $this->buatPesananSelesai(300000, $now->copy());

        // Buat 1 pesanan yang belum selesai (menunggu_pembayaran) - TIDAK boleh dihitung
        Pesanan::create([
            'kode_pesanan' => 'ORD-PENDING',
            'customer_id' => $this->customer->id,
            'layanan_id' => $this->layanan->id,
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer_id',
            'nickname_game' => 'ProGamer',
            'server_game' => '1001',
            'status' => StatusPesanan::MenungguPembayaran,
            'total_harga' => 500000,
            'batas_waktu_bayar' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan-transaksi.index', [
            'tanggal_mulai' => '2025-05-01',
            'tanggal_akhir' => '2025-05-31',
        ]));

        $response->assertOk();
        $response->assertViewHas('totalPesanan', 3);
        $response->assertViewHas('totalPendapatan', 600000);
        $response->assertViewHas('rataRataTransaksi', 200000);
        $response->assertSee('Rp 600.000');
        $response->assertSee('Rp 200.000');

        Carbon::setTestNow();
    }

    public function test_filter_periode_tanggal_berfungsi_dengan_akurat(): void
    {
        // Pesanan di April
        $this->buatPesananSelesai(150000, Carbon::parse('2025-04-10 12:00:00'));
        // Pesanan di Mei
        $this->buatPesananSelesai(250000, Carbon::parse('2025-05-15 12:00:00'));
        // Pesanan di Juni
        $this->buatPesananSelesai(350000, Carbon::parse('2025-06-20 12:00:00'));

        // Filter hanya bulan Mei 2025
        $response = $this->actingAs($this->admin)->get(route('admin.laporan-transaksi.index', [
            'tanggal_mulai' => '2025-05-01',
            'tanggal_akhir' => '2025-05-31',
        ]));

        $response->assertOk();
        $response->assertViewHas('totalPesanan', 1);
        $response->assertViewHas('totalPendapatan', 250000);
        $response->assertSee('Rp 250.000');
        $response->assertDontSee('Rp 150.000');
        $response->assertDontSee('Rp 350.000');
    }

    public function test_validasi_tanggal_akhir_tidak_boleh_sebelum_tanggal_mulai(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan-transaksi.index', [
            'tanggal_mulai' => '2025-05-10',
            'tanggal_akhir' => '2025-05-01',
        ]));

        $response->assertSessionHasErrors(['tanggal_akhir']);
    }

    public function test_menampilkan_pesan_kosong_jika_tidak_ada_transaksi(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan-transaksi.index', [
            'tanggal_mulai' => '2025-01-01',
            'tanggal_akhir' => '2025-01-02',
        ]));

        $response->assertOk();
        $response->assertViewHas('totalPesanan', 0);
        $response->assertSee('Tidak ada transaksi');
    }

    public function test_beranda_menampilkan_sidebar_ulasan_dan_rating(): void
    {
        $pesanan = $this->buatPesananSelesai(150000, now());

        Ulasan::create([
            'pesanan_id' => $pesanan->id,
            'rating' => 5,
            'komentar' => 'Pelayanan joki sangat cepat dan memuaskan!',
        ]);

        $response = $this->get(route('beranda'));

        $response->assertOk();
        $response->assertSee('Rating Pelanggan');
        $response->assertSee('5 Ulasan Terbaru');
        $response->assertSee('Pelayanan joki sangat cepat dan memuaskan!');
        $response->assertSee('5.0');
        $response->assertSee('1 ulasan terverifikasi');
    }
}
