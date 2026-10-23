<?php

namespace App\Enums;

/**
 * Status pesanan — mengatur seluruh alur kerja pesanan.
 * Dipakai di kolom pesanan.status
 */
enum StatusPesanan: string
{
    // Alur awal
    case MenungguPembayaran = 'menunggu_pembayaran';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case PerluRevisiData    = 'perlu_revisi_data';
    case SiapDitugaskan     = 'siap_ditugaskan';

    // Pengerjaan
    case Diproses           = 'diproses';
    case SedangDikerjakan   = 'sedang_dikerjakan';
    case LaporanMasuk       = 'laporan_masuk';
    case PerluRevisiHasil   = 'perlu_revisi_hasil';
    case WorkerSelesai      = 'worker_selesai';

    // Penyelesaian
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case Dikomplain         = 'dikomplain';
    case HasilDisetujui     = 'hasil_disetujui';
    case Selesai            = 'selesai';

    // Terminal (tidak bisa berubah lagi)
    case Dibatalkan         = 'dibatalkan';
    case Kedaluwarsa        = 'kedaluwarsa';

    /** Label tampilan untuk UI */
    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::PerluRevisiData    => 'Perlu Revisi Data',
            self::SiapDitugaskan     => 'Siap Ditugaskan',
            self::Diproses           => 'Diproses',
            self::SedangDikerjakan   => 'Sedang Dikerjakan',
            self::LaporanMasuk       => 'Laporan Masuk',
            self::PerluRevisiHasil   => 'Perlu Revisi Hasil',
            self::WorkerSelesai      => 'Worker Selesai',
            self::MenungguKonfirmasi => 'Menunggu Konfirmasi',
            self::Dikomplain         => 'Dikomplain',
            self::HasilDisetujui     => 'Hasil Disetujui',
            self::Dibatalkan         => 'Dibatalkan',
            self::Kedaluwarsa        => 'Kedaluwarsa',
            self::Selesai            => 'Selesai',
        };
    }

    /** Warna badge untuk tampilan (Tailwind CSS class) */
    public function warnaBadge(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'bg-yellow-600',
            self::MenungguVerifikasi => 'bg-blue-600',
            self::PerluRevisiData    => 'bg-orange-600',
            self::SiapDitugaskan     => 'bg-cyan-600',
            self::Diproses           => 'bg-indigo-600',
            self::SedangDikerjakan   => 'bg-purple-600',
            self::LaporanMasuk       => 'bg-teal-600',
            self::PerluRevisiHasil   => 'bg-orange-600',
            self::WorkerSelesai      => 'bg-emerald-600',
            self::MenungguKonfirmasi => 'bg-blue-500',
            self::Dikomplain         => 'bg-red-500',
            self::HasilDisetujui     => 'bg-green-600',
            self::Dibatalkan         => 'bg-red-700',
            self::Kedaluwarsa        => 'bg-gray-600',
            self::Selesai            => 'bg-green-700',
        };
    }

    /** Cek apakah status ini bersifat terminal (tidak bisa berubah lagi) */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Dibatalkan, self::Kedaluwarsa, self::Selesai]);
    }

    /** Status yang menandakan pesanan masih aktif (Worker masih terlibat) */
    public static function statusAktifWorker(): array
    {
        return [
            self::Diproses,
            self::SedangDikerjakan,
            self::LaporanMasuk,
            self::WorkerSelesai,
            self::MenungguKonfirmasi,
            self::Dikomplain,
            self::PerluRevisiHasil,
        ];
    }
}
