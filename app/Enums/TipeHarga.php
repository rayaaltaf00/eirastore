<?php

namespace App\Enums;

/**
 * Tipe harga layanan — menentukan cara menghitung harga pesanan.
 * Dipakai di kolom layanan.tipe_harga
 */
enum TipeHarga: string
{
    case Tangga = 'tangga';   // harga dari selisih tingkat awal ke tujuan (misal: rank ML)
    case Progres = 'progres';  // harga dasar dikurangi progres yang sudah ada (misal: explore Genshin)
    case Pilihan = 'pilihan';  // customer pilih item, harga dijumlahkan (misal: quest, achievement)

    /** Label tampilan untuk UI */
    public function label(): string
    {
        return match ($this) {
            self::Tangga => 'Tangga (Tingkatan)',
            self::Progres => 'Progres (Persentase)',
            self::Pilihan => 'Pilihan (Centang Item)',
        };
    }

    /** Penjelasan singkat untuk form Admin */
    public function deskripsi(): string
    {
        return match ($this) {
            self::Tangga => 'Harga dihitung dari selisih posisi awal ke tujuan pada tingkatan berurutan.',
            self::Progres => 'Harga dasar dikurangi progres yang sudah dimiliki customer.',
            self::Pilihan => 'Customer memilih satu atau lebih item, harga dijumlahkan.',
        };
    }
}
