<?php

namespace App\Services;

use App\Enums\TipeHarga;
use App\Models\Layanan;
use App\Models\LayananItem;
use App\Models\Pesanan;
use InvalidArgumentException;

/**
 * Layanan Kalkulasi Pesanan & Penetapan Biaya (Order Calculation Service)
 *
 * Jantung logika kalkulasi harga di sisi server (Server-Side Calculation):
 * 1. Tidak pernah mempercayai input harga dari browser/frontend
 * 2. Menghitung formula Tangga (selisih bintang/tingkatan antar tier)
 * 3. Menghitung formula Progres (eksplorasi persentase dinamis)
 * 4. Menghitung formula Pilihan (item quest/unlock lepasan + add-on)
 * 5. Generator kode pesanan unik anti-duplikasi berbasis timestamp
 */
class OrderCalculationService
{
    /**
     * Hitung rincian item dan total harga berdasarkan data resmi di database.
     * Tidak pernah mempercayai angka total yang dikirimkan oleh browser/klien.
     *
     * @return array{total_harga: float, items: array<int, array{layanan_item_id: int, peran: string, nilai: float|int|null, nama_item: string, harga_satuan: float, subtotal: float}>}
     */
    public function hitung(Layanan $layanan, array $input): array
    {
        return match ($layanan->tipe_harga) {
            TipeHarga::Tangga => $this->hitungTangga($layanan, $input),
            TipeHarga::Progres => $this->hitungProgres($layanan, $input),
            TipeHarga::Pilihan => $this->hitungPilihan($layanan, $input),
        };
    }

    /**
     * Perhitungan tipe TANGGA (misal rank bintang).
     */
    protected function hitungTangga(Layanan $layanan, array $input): array
    {
        $tierAwalId = $input['tier_awal_id'] ?? null;
        $bintangAwal = (int) ($input['bintang_awal'] ?? 0);

        $tierTujuanId = $input['tier_tujuan_id'] ?? null;
        $bintangTujuan = (int) ($input['bintang_tujuan'] ?? 0);

        if (! $tierAwalId || ! $tierTujuanId) {
            throw new InvalidArgumentException('Tingkat awal dan tingkat tujuan wajib dipilih.');
        }

        /** @var LayananItem $tierAwal */
        $tierAwal = $layanan->items()->where('jenis', 'utama')->where('id', $tierAwalId)->firstOrFail();
        /** @var LayananItem $tierTujuan */
        $tierTujuan = $layanan->items()->where('jenis', 'utama')->where('id', $tierTujuanId)->firstOrFail();

        // Validasi batasan bintang
        if ($bintangAwal < 0 || ($tierAwal->nilai_maks && $bintangAwal > $tierAwal->nilai_maks)) {
            throw new InvalidArgumentException("Bintang awal tidak valid untuk tingkat {$tierAwal->nama}.");
        }

        if ($bintangTujuan < 1 || ($tierTujuan->nilai_maks && $bintangTujuan > $tierTujuan->nilai_maks)) {
            throw new InvalidArgumentException("Bintang tujuan harus minimal 1 dan maksimal {$tierTujuan->nilai_maks}.");
        }

        // Validasi urutan tujuan harus lebih tinggi
        if ($tierTujuan->urutan < $tierAwal->urutan) {
            throw new InvalidArgumentException('Tingkat tujuan harus lebih tinggi dari tingkat awal.');
        }

        if ($tierTujuan->urutan === $tierAwal->urutan && $bintangTujuan <= $bintangAwal) {
            throw new InvalidArgumentException('Bintang tujuan harus lebih besar dari bintang saat ini pada tingkat yang sama.');
        }

        $items = [];
        $totalHarga = 0.0;

        if ($tierAwal->id === $tierTujuan->id) {
            // Berada di tier yang sama
            $langkah = $bintangTujuan - $bintangAwal;
            $subtotal = round($langkah * (float) $tierAwal->harga, 2);

            // Item awal: posisi saat ini (subtotal 0, hanya sebagai penanda)
            $items[] = [
                'layanan_item_id' => $tierAwal->id,
                'peran' => 'awal',
                'nilai' => $bintangAwal,
                'nama_item' => "{$tierAwal->nama} ({$bintangAwal} ★)",
                'harga_satuan' => 0,
                'subtotal' => 0,
            ];

            // Item tujuan: target akhir
            $items[] = [
                'layanan_item_id' => $tierAwal->id,
                'peran' => 'tujuan',
                'nilai' => $bintangTujuan,
                'nama_item' => "{$tierAwal->nama} (Target {$bintangTujuan} ★)",
                'harga_satuan' => (float) $tierAwal->harga,
                'subtotal' => $subtotal,
            ];
            $totalHarga += $subtotal;
        } else {
            // Tier awal: langkah dari bintang saat ini menuju max bintang tier awal
            $langkahAwal = max(0, (int) $tierAwal->nilai_maks - $bintangAwal);
            if ($langkahAwal > 0) {
                $subtotalAwal = round($langkahAwal * (float) $tierAwal->harga, 2);
                $items[] = [
                    'layanan_item_id' => $tierAwal->id,
                    'peran' => 'awal',
                    'nilai' => $bintangAwal,
                    'nama_item' => "{$tierAwal->nama} (Sisa {$langkahAwal} ★ dari {$bintangAwal} ★)",
                    'harga_satuan' => (float) $tierAwal->harga,
                    'subtotal' => $subtotalAwal,
                ];
                $totalHarga += $subtotalAwal;
            } else {
                // Posisi awal pas di nilai_maks (0 langkah di tier awal, hanya sebagai penanda titik awal)
                $items[] = [
                    'layanan_item_id' => $tierAwal->id,
                    'peran' => 'awal',
                    'nilai' => $bintangAwal,
                    'nama_item' => "{$tierAwal->nama} ({$bintangAwal} ★)",
                    'harga_satuan' => 0,
                    'subtotal' => 0,
                ];
            }

            // Tier di antara (jika ada)
            $tierAntara = $layanan->items()
                ->where('jenis', 'utama')
                ->where('urutan', '>', $tierAwal->urutan)
                ->where('urutan', '<', $tierTujuan->urutan)
                ->orderBy('urutan')
                ->get();

            foreach ($tierAntara as $antara) {
                $langkahAntara = (int) $antara->nilai_maks;
                $subtotalAntara = round($langkahAntara * (float) $antara->harga, 2);
                $items[] = [
                    'layanan_item_id' => $antara->id,
                    'peran' => 'dipilih',
                    'nilai' => $langkahAntara,
                    'nama_item' => "{$antara->nama} (Penuh {$langkahAntara} ★)",
                    'harga_satuan' => (float) $antara->harga,
                    'subtotal' => $subtotalAntara,
                ];
                $totalHarga += $subtotalAntara;
            }

            // Tier tujuan: dari 0 menuju target bintang
            $langkahTujuan = $bintangTujuan;
            $subtotalTujuan = round($langkahTujuan * (float) $tierTujuan->harga, 2);
            $items[] = [
                'layanan_item_id' => $tierTujuan->id,
                'peran' => 'tujuan',
                'nilai' => $langkahTujuan,
                'nama_item' => "{$tierTujuan->nama} (Target {$langkahTujuan} ★)",
                'harga_satuan' => (float) $tierTujuan->harga,
                'subtotal' => $subtotalTujuan,
            ];
            $totalHarga += $subtotalTujuan;
        }

        // Item tambahan opsional
        $this->prosesItemTambahan($layanan, $input, $items, $totalHarga);

        return [
            'total_harga' => $totalHarga,
            'items' => $items,
        ];
    }

    /**
     * Perhitungan tipe PROGRES (misal eksplorasi wilayah 100%).
     */
    protected function hitungProgres(Layanan $layanan, array $input): array
    {
        $rawProgres = $input['progres'] ?? [];
        if (! is_array($rawProgres) || empty($rawProgres)) {
            throw new InvalidArgumentException('Pilihlah minimal satu wilayah dan tentukan progresnya.');
        }

        // Filter hanya item yang dicentang / dipilih bila form mengirimkan array progres_selected
        $selectedIds = $input['progres_selected'] ?? null;
        if (is_array($selectedIds)) {
            $rawProgres = array_intersect_key($rawProgres, array_flip($selectedIds));
        }

        $items = [];
        $totalHarga = 0.0;

        foreach ($rawProgres as $itemId => $progresSaatIni) {
            if (is_array($progresSaatIni)) {
                $progresVal = (float) ($progresSaatIni['awal'] ?? ($progresSaatIni['current'] ?? 0));
                $targetVal = (float) ($progresSaatIni['tujuan'] ?? ($progresSaatIni['target'] ?? 100));
            } else {
                $progresVal = (float) $progresSaatIni;
                $targetVal = 100.0;
            }

            if ($progresVal < 0 || $progresVal >= $targetVal) {
                continue; // Lewati jika di luar rentang atau target sudah tercapai
            }

            /** @var LayananItem|null $item */
            $item = $layanan->items()->where('id', $itemId)->first();
            if (! $item || (float) $item->harga <= 0) {
                continue;
            }

            // Sisa pengerjaan: (target - progresSaatIni) %
            $sisaPersen = min(100.0, max(0.0, $targetVal - $progresVal));
            $subtotal = round(($sisaPersen / 100) * (float) $item->harga, 2);

            $items[] = [
                'layanan_item_id' => $item->id,
                'peran' => 'dipilih',
                'nilai' => $progresVal,
                'nama_item' => "{$item->nama} (Sisa {$sisaPersen}% dari {$progresVal}%)",
                'harga_satuan' => (float) $item->harga,
                'subtotal' => $subtotal,
            ];
            $totalHarga += $subtotal;
        }

        if (empty($items)) {
            throw new InvalidArgumentException('Pilihlah minimal satu wilayah dengan progres di bawah 100%.');
        }

        // Item tambahan opsional
        $this->prosesItemTambahan($layanan, $input, $items, $totalHarga);

        return [
            'total_harga' => $totalHarga,
            'items' => $items,
        ];
    }

    /**
     * Perhitungan tipe PILIHAN (misal paket match, quest, mabar).
     */
    protected function hitungPilihan(Layanan $layanan, array $input): array
    {
        $itemSelections = $input['items'] ?? [];
        if (! is_array($itemSelections) || empty($itemSelections)) {
            throw new InvalidArgumentException('Pilihlah minimal satu paket atau item layanan.');
        }

        $items = [];
        $totalHarga = 0.0;

        foreach ($itemSelections as $key => $val) {
            if (is_array($val)) {
                $itemId = $val['id'] ?? (is_numeric($key) ? null : $key);
                $jumlah = (int) ($val['jumlah'] ?? 1);
            } elseif (is_numeric($key) && is_numeric($val) && array_is_list($itemSelections)) {
                // Format: [10, 12] -> $key = index urut, $val = ID item layanan
                $itemId = (int) $val;
                $jumlah = 1;
            } else {
                // Format: ['10' => 3] -> $key = ID item, $val = jumlah
                $itemId = is_numeric($key) ? (int) $key : (is_numeric($val) ? (int) $val : null);
                $jumlah = is_numeric($val) && ! array_is_list($itemSelections) ? (int) $val : 1;
            }

            if (! $itemId) {
                continue;
            }

            /** @var LayananItem|null $item */
            $item = $layanan->items()->where('jenis', 'utama')->where('id', $itemId)->first();
            if (! $item) {
                continue;
            }

            $jumlah = max(1, $jumlah);
            if ($item->nilai_maks && $jumlah > $item->nilai_maks) {
                $jumlah = (int) $item->nilai_maks;
            }

            $subtotal = round($jumlah * (float) $item->harga, 2);

            $items[] = [
                'layanan_item_id' => $item->id,
                'peran' => 'dipilih',
                'nilai' => $jumlah,
                'nama_item' => $jumlah > 1 ? "{$item->nama} (x{$jumlah} {$item->satuan})" : $item->nama,
                'harga_satuan' => (float) $item->harga,
                'subtotal' => $subtotal,
            ];
            $totalHarga += $subtotal;
        }

        if (empty($items)) {
            throw new InvalidArgumentException('Pilihan paket atau item layanan tidak valid.');
        }

        // Item tambahan opsional
        $this->prosesItemTambahan($layanan, $input, $items, $totalHarga);

        return [
            'total_harga' => $totalHarga,
            'items' => $items,
        ];
    }

    /**
     * Proses item tambahan opsional jika ada.
     */
    protected function prosesItemTambahan(Layanan $layanan, array $input, array &$items, float &$totalHarga): void
    {
        $tambahanIds = $input['item_tambahan_ids'] ?? [];
        if (! is_array($tambahanIds) || empty($tambahanIds)) {
            return;
        }

        $tambahanItems = $layanan->items()
            ->where('jenis', 'tambahan')
            ->whereIn('id', $tambahanIds)
            ->get();

        foreach ($tambahanItems as $tambahan) {
            $subtotal = (float) $tambahan->harga;
            $items[] = [
                'layanan_item_id' => $tambahan->id,
                'peran' => 'tambahan',
                'nilai' => 1,
                'nama_item' => "[Tambahan] {$tambahan->nama}",
                'harga_satuan' => $subtotal,
                'subtotal' => $subtotal,
            ];
            $totalHarga += $subtotal;
        }
    }

    /**
     * Generate kode pesanan unik dengan format: ES-YYYYMMDD-XXX
     */
    public function generateKodePesanan(): string
    {
        return Pesanan::generateKodePesanan();
    }
}
