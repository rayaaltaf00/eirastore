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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerCompletionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $worker;
    private User $otherCustomer;
    private Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->customer = User::factory()->create([
            'role' => Role::Customer,
            'email' => 'customer@example.com',
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => Role::Customer,
            'email' => 'other@example.com',
        ]);

        $this->worker = User::factory()->create([
            'role' => Role::Worker,
            'email' => 'worker@example.com',
        ]);

        $game = Game::create([
            'nama_game' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'kategori' => 'MOBA',
            'gambar' => 'games/ml.jpg',
            'aktif' => true,
        ]);

        $this->layanan = Layanan::create([
            'game_id' => $game->id,
            'nama_layanan' => 'Joki Rank Legend ke Mythic',
            'tipe_harga' => TipeHarga::Tangga,
            'aktif' => true,
        ]);
    }

    private function buatPesanan(StatusPesanan $status, array $override = []): Pesanan
    {
        return Pesanan::create(array_merge([
            'kode_pesanan' => 'ES-' . now()->format('Ymd') . '-' . str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT),
            'customer_id' => $this->customer->id,
            'worker_id' => $this->worker->id,
            'layanan_id' => $this->layanan->id,
            'id_akun_game' => '12345678',
            'login_via' => 'Moonton',
            'username_akun_game' => 'gamer_pro@gmail.com',
            'password_akun_game' => 'Secret123!',
            'nickname_game' => 'ProGamer',
            'server_game' => '1001',
            'catatan_customer' => 'Jangan main malam',
            'total_harga' => 150000,
            'status' => $status,
            'batas_waktu_bayar' => now()->addHours(2),
        ], $override));
    }

    public function test_customer_dapat_menyetujui_hasil_joki_dan_password_dihapus(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::MenungguKonfirmasi);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.setujui-hasil', $pesanan->kode_pesanan));

        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::HasilDisetujui, $pesanan->status);
        $this->assertNull($pesanan->password_akun_game);
        $this->assertNotNull($pesanan->kredensial_dihapus_at);

        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'pesanan_id' => $pesanan->id,
            'status_baru' => StatusPesanan::HasilDisetujui->value,
            'diubah_oleh' => $this->customer->id,
        ]);
    }

    public function test_user_lain_tidak_dapat_menyetujui_hasil_pesanan(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::MenungguKonfirmasi);

        $response = $this->actingAs($this->otherCustomer)
            ->post(route('pesanan.setujui-hasil', $pesanan->kode_pesanan));

        $response->assertStatus(403);
    }

    public function test_tidak_dapat_menyetujui_hasil_jika_status_bukan_menunggu_konfirmasi(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::SedangDikerjakan);

        $response = $this->actingAs($this->customer)
            ->from(route('pesanan.show', $pesanan->kode_pesanan))
            ->post(route('pesanan.setujui-hasil', $pesanan->kode_pesanan));

        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
        $response->assertSessionHas('error');
    }

    public function test_customer_dapat_mengajukan_komplain_dengan_alasan_valid(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::MenungguKonfirmasi);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.ajukan-komplain', $pesanan->kode_pesanan), [
                'alasan' => 'Target rank belum tercapai, bintang masih kurang 2 bintang lagi.',
            ]);

        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::Dikomplain, $pesanan->status);
        // Password harus tetap dipertahankan untuk rework
        $this->assertNotNull($pesanan->password_akun_game);

        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'pesanan_id' => $pesanan->id,
            'status_baru' => StatusPesanan::Dikomplain->value,
            'diubah_oleh' => $this->customer->id,
        ]);
    }

    public function test_pengajuan_komplain_gagal_jika_alasan_kurang_dari_10_karakter(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::MenungguKonfirmasi);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.ajukan-komplain', $pesanan->kode_pesanan), [
                'alasan' => 'Kurang',
            ]);

        $response->assertSessionHasErrors('alasan');
        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::MenungguKonfirmasi, $pesanan->status);
    }

    public function test_customer_dapat_konfirmasi_selesai_setelah_hasil_disetujui(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::HasilDisetujui, [
            'password_akun_game' => null,
            'kredensial_dihapus_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.konfirmasi-selesai', $pesanan->kode_pesanan));

        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
        $response->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertEquals(StatusPesanan::Selesai, $pesanan->status);
        $this->assertNotNull($pesanan->selesai_at);

        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'pesanan_id' => $pesanan->id,
            'status_baru' => StatusPesanan::Selesai->value,
            'diubah_oleh' => $this->customer->id,
        ]);
    }

    public function test_customer_dapat_memberikan_ulasan_dan_rating_pada_pesanan_selesai(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::Selesai, [
            'selesai_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.simpan-ulasan', $pesanan->kode_pesanan), [
                'rating' => 5,
                'komentar' => 'Mantap sekali pengerjaan sangat cepat dan win rate tinggi!',
            ]);

        $response->assertRedirect(route('pesanan.show', $pesanan->kode_pesanan));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ulasan', [
            'pesanan_id' => $pesanan->id,
            'rating' => 5,
            'komentar' => 'Mantap sekali pengerjaan sangat cepat dan win rate tinggi!',
        ]);

        // Coba kirim ulasan kedua kali (harus ditolak)
        $responseSecond = $this->actingAs($this->customer)
            ->post(route('pesanan.simpan-ulasan', $pesanan->kode_pesanan), [
                'rating' => 4,
                'komentar' => 'Ulasan kedua coba-coba',
            ]);

        $responseSecond->assertSessionHas('error');
    }

    public function test_customer_dapat_melihat_bukti_selesai_laporan_worker(): void
    {
        $pesanan = $this->buatPesanan(StatusPesanan::MenungguKonfirmasi);
        
        $fakeFile = UploadedFile::fake()->image('bukti_akhir.png');
        $storedPath = $fakeFile->store('bukti_selesai', 'local');

        LaporanPenyelesaian::create([
            'pesanan_id' => $pesanan->id,
            'worker_id' => $this->worker->id,
            'foto_bukti' => $storedPath,
            'catatan' => 'Selesai mythic 25 bintang',
            'status' => StatusLaporan::Terverifikasi,
            'diverifikasi_at' => now(),
        ]);

        // Customer pemilik pesanan dapat melihat
        $response = $this->actingAs($this->customer)
            ->get(route('pesanan.bukti-selesai', $pesanan->kode_pesanan));

        $response->assertOk();

        // Customer lain ditolak
        $responseOther = $this->actingAs($this->otherCustomer)
            ->get(route('pesanan.bukti-selesai', $pesanan->kode_pesanan));

        $responseOther->assertStatus(403);
    }

    public function test_artisan_pesanan_auto_selesai_berhasil_menyelesaikan_pesanan_24_jam(): void
    {
        // 1. Pesanan menunggu konfirmasi yang diverifikasi lebih dari 24 jam lalu
        $pesananLewat24Jam = $this->buatPesanan(StatusPesanan::MenungguKonfirmasi);
        $pesananLewat24Jam->laporanTerbaru()->create([
            'worker_id' => $this->worker->id,
            'foto_bukti' => 'bukti_selesai/dummy.png',
            'catatan' => 'Done',
            'status' => StatusLaporan::Terverifikasi,
            'diverifikasi_at' => Carbon::now()->subHours(25),
        ]);
        // Update updated_at juga untuk memastikan
        $pesananLewat24Jam->updated_at = Carbon::now()->subHours(25);
        $pesananLewat24Jam->save();

        // 2. Pesanan menunggu konfirmasi yang baru 2 jam lalu (belum 24 jam)
        $pesananBaru2Jam = $this->buatPesanan(StatusPesanan::MenungguKonfirmasi);
        $pesananBaru2Jam->laporanTerbaru()->create([
            'worker_id' => $this->worker->id,
            'foto_bukti' => 'bukti_selesai/dummy2.png',
            'catatan' => 'Done baru',
            'status' => StatusLaporan::Terverifikasi,
            'diverifikasi_at' => Carbon::now()->subHours(2),
        ]);
        $pesananBaru2Jam->updated_at = Carbon::now()->subHours(2);
        $pesananBaru2Jam->save();

        // Jalankan perintah auto-selesai
        $this->artisan('pesanan:auto-selesai')
            ->expectsOutputToContain('Berhasil memproses 1 pesanan auto-selesai')
            ->assertSuccessful();

        $pesananLewat24Jam->refresh();
        $pesananBaru2Jam->refresh();

        $this->assertEquals(StatusPesanan::Selesai, $pesananLewat24Jam->status);
        $this->assertNull($pesananLewat24Jam->password_akun_game);
        $this->assertNotNull($pesananLewat24Jam->selesai_at);

        $this->assertEquals(StatusPesanan::MenungguKonfirmasi, $pesananBaru2Jam->status);
    }
}
