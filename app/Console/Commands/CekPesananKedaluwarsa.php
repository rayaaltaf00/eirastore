<?php

namespace App\Console\Commands;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $count = 0;

        foreach ($pesananKedaluwarsa as $pesanan) {
            DB::transaction(function () use ($pesanan, &$count) {
                $lockedPesanan = Pesanan::where('id', $pesanan->id)->lockForUpdate()->first();
                if (! $lockedPesanan || $lockedPesanan->status !== StatusPesanan::MenungguPembayaran) {
                    return;
                }

                $lockedPesanan->update([
                    'status' => StatusPesanan::Kedaluwarsa,
                ]);

                // Hapus password akun game total demi keamanan privasi pengguna
                $lockedPesanan->hapusPasswordAkunGame();

                // Catat ke timeline riwayat status
                $lockedPesanan->catatRiwayat(
                    StatusPesanan::MenungguPembayaran,
                    StatusPesanan::Kedaluwarsa,
                    null,
                    'Batas waktu pembayaran 2 jam telah habis. Pesanan otomatis kedaluwarsa oleh sistem dan kredensial game telah dihapus.'
                );

                $count++;
            });
        }

        $this->info("Berhasil memproses {$count} pesanan kedaluwarsa.");

        return self::SUCCESS;
    }
}
