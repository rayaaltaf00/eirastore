<?php

namespace App\Console\Commands;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Command Otomatisasi: Penyelesaian Pesanan Tanpa Respon (24 Jam)
 *
 * Alur Kerja:
 * 1. Menjaring pesanan berstatus 'menunggu_konfirmasi' di mana hasil joki sudah dikirimkan ke pelanggan lebih dari 24 jam lalu.
 * 2. Memeriksa waktu transisi terakhir ke 'menunggu_konfirmasi' agar akurat jika sebelumnya sempat terjadi revisi/komplain.
 * 3. Menghindari race condition dengan transaksi atomik dan lockForUpdate (jika pelanggan sedang menekan tombol komplain/setuju).
 * 4. Mengubah status pesanan menjadi 'selesai', mengisi waktu selesai_at, dan menghapus permanen password akun game pelanggan.
 * 5. Menambahkan log audit ke riwayat perubahan status.
 */
#[Signature('pesanan:auto-selesai')]
#[Description('Otomatis menyelesaikan pesanan berstatus menunggu_konfirmasi yang melewati batas 24 jam tanpa konfirmasi atau komplain dari customer.')]
class AutoCompletePesananCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batasWaktu = now()->subHours(24);

        // Ambil pesanan menunggu_konfirmasi dan filter berdasarkan riwayat transisi terakhir
        $pesanans = Pesanan::query()
            ->where('status', StatusPesanan::MenungguKonfirmasi)
            ->with(['riwayatStatus' => fn ($q) => $q->orderByDesc('id')])
            ->get()
            ->filter(function (Pesanan $pesanan) use ($batasWaktu) {
                // Ambil riwayat TERAKHIR transisi ke MenungguKonfirmasi
                $riwayatTerakhir = $pesanan->riwayatStatus->first(function ($r) {
                    $val = $r->status_baru instanceof \BackedEnum ? $r->status_baru->value : $r->status_baru;

                    return $val === StatusPesanan::MenungguKonfirmasi->value;
                });

                if ($riwayatTerakhir) {
                    return $riwayatTerakhir->created_at <= $batasWaktu;
                }

                // Fallback hanya jika tidak ada entri riwayat ke MenungguKonfirmasi sama sekali
                return $pesanan->updated_at <= $batasWaktu;
            });

        $count = 0;

        foreach ($pesanans as $pesanan) {
            DB::transaction(function () use ($pesanan, &$count) {
                $lockedPesanan = Pesanan::where('id', $pesanan->id)->lockForUpdate()->first();
                if (! $lockedPesanan || $lockedPesanan->status !== StatusPesanan::MenungguKonfirmasi) {
                    return;
                }

                $lockedPesanan->update([
                    'status' => StatusPesanan::Selesai,
                    'selesai_at' => now(),
                ]);

                // Pastikan password akun game dihapus total
                $lockedPesanan->hapusPasswordAkunGame();

                // Catat ke timeline riwayat status
                $lockedPesanan->catatRiwayat(
                    StatusPesanan::MenungguKonfirmasi,
                    StatusPesanan::Selesai,
                    null,
                    'Pesanan otomatis diselesaikan oleh sistem setelah 24 jam menunggu konfirmasi pelanggan. Kredensial akun game dihapus total.'
                );

                $count++;
            });
        }

        $this->info("Berhasil memproses {$count} pesanan auto-selesai.");

        return self::SUCCESS;
    }
}
