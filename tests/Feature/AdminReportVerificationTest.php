<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusLaporan;
use App\Enums\StatusPesanan;
use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\LaporanPenyelesaian;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminReportVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $worker;
    private User $customer;
    private Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

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
            'gambar' => 'games/ml.png',
            'aktif' => true,
        ]);

        $this->layanan = Layanan::create([
            'game_id' => $game->id,
            'nama_layanan' => 'Joki Rank Mythic',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);
    }

    private function createPesananWithLaporan(StatusPesanan $statusPesanan = StatusPesanan::LaporanMasuk): array
    {
        $pesanan = Pesanan::create([
            'kode_pesanan' => 'EIRA-RPT-001',
            'customer_id' => $this->customer->id,
            'worker_id' => $this->worker->id,
            'layanan_id' => $this->layanan->id,
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'acc@game.com',
            'nickname_game' => 'ProPlayer',
            'server_game' => '1001',
            'password_akun_game' => 'secretGamePass123',
            'catatan_pelanggan' => null,
            'status' => $statusPesanan,
            'total_harga' => 150000,
            'batas_waktu_bayar' => now()->addHours(2),
        ]);

        $path = 'bukti_selesai/dummy_proof.jpg';
        Storage::disk('local')->put($path, 'dummy image content');

        $laporan = LaporanPenyelesaian::create([
            'pesanan_id' => $pesanan->id,
            'worker_id' => $this->worker->id,
            'foto_bukti' => $path,
            'catatan' => 'Rank Mythic 25 bintang sudah tercapai mantap.',
            'status' => StatusLaporan::MenungguVerifikasi,
        ]);

        return [$pesanan, $laporan];
    }

    public function test_admin_dapat_melihat_daftar_laporan_worker_a26(): void
    {
        [$pesanan, $laporan] = $this->createPesananWithLaporan();

        $response = $this->actingAs($this->admin)->get(route('admin.laporan-worker.index'));

        $response->assertOk();
        $response->assertSee('Laporan Penyelesaian Worker (A-26)');
        $response->assertSee('EIRA-RPT-001');
        $response->assertSee('Worker MLBB Eira');
        $response->assertSee('Menunggu Verifikasi');
    }

    public function test_admin_dapat_memverifikasi_laporan_hasil_worker_a27(): void
    {
        [$pesanan, $laporan] = $this->createPesananWithLaporan(StatusPesanan::LaporanMasuk);

        $response = $this->actingAs($this->admin)->post(
            route('admin.pesanan.verifikasi-laporan', $pesanan->kode_pesanan)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $laporan->refresh();

        $this->assertEquals(StatusPesanan::WorkerSelesai, $pesanan->status);
        $this->assertEquals(StatusLaporan::Terverifikasi, $laporan->status);
        $this->assertNotNull($laporan->diverifikasi_at);
        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'pesanan_id' => $pesanan->id,
            'status_baru' => StatusPesanan::WorkerSelesai->value,
        ]);
    }

    public function test_admin_dapat_meminta_revisi_hasil_pengerjaan_worker_a29(): void
    {
        [$pesanan, $laporan] = $this->createPesananWithLaporan(StatusPesanan::LaporanMasuk);

        $response = $this->actingAs($this->admin)->post(
            route('admin.pesanan.minta-revisi-hasil', $pesanan->kode_pesanan),
            ['catatan' => 'Bintang masih kurang 2 bintang lagi, tolong lengkapi sampai 25 bintang.']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $laporan->refresh();

        $this->assertEquals(StatusPesanan::PerluRevisiHasil, $pesanan->status);
        $this->assertEquals(StatusLaporan::Ditolak, $laporan->status);
        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'pesanan_id' => $pesanan->id,
            'status_baru' => StatusPesanan::PerluRevisiHasil->value,
            'catatan' => 'Permintaan Revisi Hasil untuk Worker: Bintang masih kurang 2 bintang lagi, tolong lengkapi sampai 25 bintang.',
        ]);
    }

    public function test_admin_dapat_mengirim_notifikasi_penyelesaian_ke_pelanggan_a28(): void
    {
        [$pesanan, $laporan] = $this->createPesananWithLaporan(StatusPesanan::WorkerSelesai);

        $response = $this->actingAs($this->admin)->post(
            route('admin.pesanan.kirim-notifikasi-selesai', $pesanan->kode_pesanan),
            ['pesan_singkat' => 'Halo kak, joki rank MLBB sudah selesai ya, mohon dicek.']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();

        $this->assertEquals(StatusPesanan::MenungguKonfirmasi, $pesanan->status);
        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'pesanan_id' => $pesanan->id,
            'status_baru' => StatusPesanan::MenungguKonfirmasi->value,
        ]);
    }

    public function test_admin_dapat_melihat_file_bukti_penyelesaian_worker(): void
    {
        [$pesanan, $laporan] = $this->createPesananWithLaporan();

        $response = $this->actingAs($this->admin)->get(
            route('admin.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $laporan->id])
        );

        $response->assertOk();
    }

    public function test_non_admin_tidak_dapat_mengakses_laporan_worker(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.laporan-worker.index'));
        $response->assertForbidden();

        $responseWorker = $this->actingAs($this->worker)->get(route('admin.laporan-worker.index'));
        $responseWorker->assertForbidden();
    }
}
