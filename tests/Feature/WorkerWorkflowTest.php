<?php

namespace Tests\Feature;

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

class WorkerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $worker;

    private User $workerLain;

    private User $customer;

    private Game $game;

    private Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->worker = User::factory()->create([
            'role' => 'worker',
            'status_akun' => 'aktif',
        ]);

        $this->workerLain = User::factory()->create([
            'role' => 'worker',
            'status_akun' => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->game = Game::create([
            'nama_game' => 'Mobile Legends: Bang Bang',
            'slug' => 'mobile-legends',
            'kategori' => 'MOBA',
            'gambar' => 'games/ml.jpg',
            'aktif' => true,
        ]);

        $this->layanan = Layanan::create([
            'game_id' => $this->game->id,
            'nama_layanan' => 'Joki Rank Bintang',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);
    }

    private function buatPesanan(array $attributes = []): Pesanan
    {
        return Pesanan::create(array_merge([
            'kode_pesanan' => 'ES-'.now()->format('Ymd').'-'.str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
            'customer_id' => $this->customer->id,
            'worker_id' => $this->worker->id,
            'layanan_id' => $this->layanan->id,
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer_pro@gmail.com',
            'password_akun_game' => 'RahasiaAkun123!',
            'nickname_game' => 'DragonSlayer',
            'server_game' => '1001',
            'total_harga' => 150000,
            'status' => StatusPesanan::Diproses,
            'batas_waktu_bayar' => now()->addHours(2),
        ], $attributes));
    }

    /** W-03: Worker hanya melihat pesanan yang ditugaskan ke dirinya */
    public function test_worker_hanya_melihat_pesanan_yang_ditugaskan_kepadanya(): void
    {
        $pesananMiliknya = $this->buatPesanan(['status' => StatusPesanan::Diproses]);
        $pesananLain = $this->buatPesanan([
            'kode_pesanan' => 'ES-20261026-999',
            'worker_id' => $this->workerLain->id,
            'id_akun_game' => 'AkunPunyaOrangLain',
        ]);

        $response = $this->actingAs($this->worker)->get(route('worker.pesanan'));

        $response->assertStatus(200);
        $response->assertSee($pesananMiliknya->kode_pesanan);
        $response->assertDontSee($pesananLain->kode_pesanan);
    }

    /** W-03: Worker dilarang mengakses detail pesanan milik worker lain */
    public function test_worker_dilarang_mengakses_pesanan_worker_lain(): void
    {
        $pesananLain = $this->buatPesanan([
            'worker_id' => $this->workerLain->id,
        ]);

        $response = $this->actingAs($this->worker)->get(route('worker.pesanan.show', $pesananLain->kode_pesanan));

        $response->assertStatus(403);
    }

    /** W-03: Worker dapat melihat kredensial akun dan password terdekripsi */
    public function test_worker_dapat_melihat_detail_dan_password_akun_game(): void
    {
        $pesanan = $this->buatPesanan([
            'password_akun_game' => 'PasswordSuperSecret99',
        ]);

        $response = $this->actingAs($this->worker)->get(route('worker.pesanan.show', $pesanan->kode_pesanan));

        $response->assertStatus(200);
        $response->assertSee('gamer_pro@gmail.com');
        $response->assertSee('PasswordSuperSecret99');
    }

    /** W-04: Worker dapat memulai pengerjaan (diproses -> sedang_dikerjakan) */
    public function test_worker_dapat_memulai_pengerjaan_mengubah_status(): void
    {
        $pesanan = $this->buatPesanan(['status' => StatusPesanan::Diproses]);

        $response = $this->actingAs($this->worker)
            ->post(route('worker.pesanan.mulai', $pesanan->kode_pesanan));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::SedangDikerjakan, $pesanan->status);

        // Pastikan timeline riwayat tercatat
        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'pesanan_id' => $pesanan->id,
            'status_lama' => StatusPesanan::Diproses->value,
            'status_baru' => StatusPesanan::SedangDikerjakan->value,
            'diubah_oleh' => $this->worker->id,
        ]);
    }

    /** W-05: Worker dapat mengunggah foto bukti penyelesaian (sedang_dikerjakan -> laporan_masuk) */
    public function test_worker_dapat_mengunggah_foto_bukti_penyelesaian(): void
    {
        $pesanan = $this->buatPesanan(['status' => StatusPesanan::SedangDikerjakan]);

        $file = UploadedFile::fake()->image('bukti_victory.png', 800, 600);

        $response = $this->actingAs($this->worker)
            ->post(route('worker.pesanan.lapor', $pesanan->kode_pesanan), [
                'foto_bukti' => $file,
                'catatan' => 'Selesai joki bintang 25, win rate mantap.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::LaporanMasuk, $pesanan->status);

        // Pastikan tersimpan di database laporan_penyelesaian
        $this->assertDatabaseHas('laporan_penyelesaian', [
            'pesanan_id' => $pesanan->id,
            'worker_id' => $this->worker->id,
            'status' => StatusLaporan::MenungguVerifikasi->value,
            'catatan' => 'Selesai joki bintang 25, win rate mantap.',
        ]);

        $laporan = LaporanPenyelesaian::where('pesanan_id', $pesanan->id)->first();
        $this->assertNotNull($laporan);
        Storage::disk('local')->assertExists($laporan->foto_bukti);
    }

    /** W-06: Worker dapat mengirim bukti revisi saat status perlu_revisi_hasil */
    public function test_worker_dapat_mengirim_bukti_revisi(): void
    {
        $pesanan = $this->buatPesanan(['status' => StatusPesanan::PerluRevisiHasil]);

        $fileRevisi = UploadedFile::fake()->image('bukti_revisi.jpg', 800, 600);

        $response = $this->actingAs($this->worker)
            ->post(route('worker.pesanan.lapor', $pesanan->kode_pesanan), [
                'foto_bukti' => $fileRevisi,
                'catatan' => 'Sudah saya perbaiki 2 bintang yang kurang.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::LaporanMasuk, $pesanan->status);

        $this->assertDatabaseHas('laporan_penyelesaian', [
            'pesanan_id' => $pesanan->id,
            'worker_id' => $this->worker->id,
            'catatan' => 'Sudah saya perbaiki 2 bintang yang kurang.',
        ]);
    }

    /** Worker dapat mengunduh / melihat foto bukti laporan miliknya */
    public function test_worker_dapat_melihat_foto_bukti_laporan(): void
    {
        $pesanan = $this->buatPesanan(['status' => StatusPesanan::LaporanMasuk]);
        $path = UploadedFile::fake()->image('bukti.png')->store('bukti_selesai', 'local');

        $laporan = LaporanPenyelesaian::create([
            'pesanan_id' => $pesanan->id,
            'worker_id' => $this->worker->id,
            'foto_bukti' => $path,
            'status' => StatusLaporan::MenungguVerifikasi,
        ]);

        $response = $this->actingAs($this->worker)
            ->get(route('worker.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $laporan->id]));

        $response->assertStatus(200);
    }

    /** Target dan item joki terdeteksi dengan tepat di daftar pesanan worker */
    public function test_worker_dapat_melihat_target_dan_item_joki_di_daftar_pesanan(): void
    {
        $pesanan = $this->buatPesanan(['status' => StatusPesanan::Diproses]);
        $pesanan->pesananItems()->create([
            'nama_item' => 'Mythic 10 Bintang ke 25 Bintang',
            'peran' => 'tujuan',
            'nilai' => 25,
            'harga_satuan' => 150000,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->worker)->get(route('worker.pesanan'));

        $response->assertStatus(200);
        $response->assertSee('Mythic 10 Bintang ke 25 Bintang');
    }

    /** Detail target paket dan item terdeteksi lengkap pada worksheet detail pesanan */
    public function test_worker_melihat_rincian_target_dan_item_pada_detail_pesanan(): void
    {
        $pesanan = $this->buatPesanan(['status' => StatusPesanan::SedangDikerjakan]);

        // Buat item awal (penanda titik awal)
        $pesanan->pesananItems()->create([
            'nama_item' => 'Mythic (10 ★)',
            'peran' => 'awal',
            'nilai' => 10,
            'harga_satuan' => 0,
            'subtotal' => 0,
        ]);

        // Buat item tujuan (target akhir)
        $pesanan->pesananItems()->create([
            'nama_item' => 'Mythic (Target 25 ★)',
            'peran' => 'tujuan',
            'nilai' => 25,
            'harga_satuan' => 150000,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->worker)->get(route('worker.pesanan.show', $pesanan->kode_pesanan));

        $response->assertStatus(200);
        // Ringkasan tangga: DARI → KE
        $response->assertSee('DARI');
        $response->assertSee('Mythic (10 ★)');
        $response->assertSee('KE');
        $response->assertSee('Mythic (Target 25 ★)');
    }
}
