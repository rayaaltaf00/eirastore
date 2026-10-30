<?php

namespace App\Console\Commands;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\RiwayatStatusPesanan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $count = $pesanans->count();

        foreach ($pesanans as $pesanan) {
            DB::transaction(function () use ($pesanan) {
                $pesanan->update([
                    'status' => StatusPesanan::Selesai,
                    'selesai_at' => now(),
                ]);

                // Pastikan password akun game dihapus total
                $pesanan->hapusPasswordAkunGame();

                // Catat ke timeline riwayat status
                RiwayatStatusPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'status_lama' => StatusPesanan::MenungguKonfirmasi->value,
                    'status_baru' => StatusPesanan::Selesai->value,
                    'catatan' => 'Pesanan otomatis diselesaikan oleh sistem setelah 24 jam menunggu konfirmasi pelanggan. Kredensial akun game dihapus total.',
                    'diubah_oleh' => null, // Sistem
                ]);
            });
        }

        $this->info("Berhasil memproses {$count} pesanan auto-selesai.");

        return self::SUCCESS;
    }
}
