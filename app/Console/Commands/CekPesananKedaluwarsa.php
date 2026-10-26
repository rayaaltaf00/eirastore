<?php

namespace App\Console\Commands;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\RiwayatStatusPesanan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pesanan:cek-kedaluwarsa')]
#[Description('Otomatis mengubah pesanan berstatus menunggu_pembayaran yang melewati batas waktu bayar menjadi kedaluwarsa dan menghapus kredensial password game.')]
class CekPesananKedaluwarsa extends Command
{
    /**
     * Alias command sesuai PRD 3.7 (pesanan:kedaluwarsakan)
     */
    protected $aliases = ['pesanan:kedaluwarsakan'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pesananKedaluwarsa = Pesanan::query()
            ->where('status', StatusPesanan::MenungguPembayaran)
            ->where('batas_waktu_bayar', '<', now())
            ->get();

        $count = $pesananKedaluwarsa->count();

        foreach ($pesananKedaluwarsa as $pesanan) {
            $pesanan->update([
                'status' => StatusPesanan::Kedaluwarsa,
            ]);

            // Hapus password akun game total demi keamanan privasi pengguna
            $pesanan->hapusPasswordAkunGame();

            // Catat ke timeline riwayat status
            RiwayatStatusPesanan::create([
                'pesanan_id' => $pesanan->id,
                'status_lama' => StatusPesanan::MenungguPembayaran->value,
                'status_baru' => StatusPesanan::Kedaluwarsa->value,
                'catatan' => 'Batas waktu pembayaran 2 jam telah habis. Pesanan otomatis kedaluwarsa oleh sistem dan kredensial game telah dihapus.',
                'diubah_oleh' => null, // Sistem
            ]);
        }

        $this->info("Berhasil memproses {$count} pesanan kedaluwarsa.");

        return self::SUCCESS;
    }
}
