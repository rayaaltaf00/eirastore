<?php

namespace App\Enums;

/**
 * Status laporan penyelesaian Worker.
 * Dipakai di kolom laporan_penyelesaian.status
 */
enum StatusLaporan: string
{
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Terverifikasi      = 'terverifikasi';
    case Ditolak            = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Terverifikasi      => 'Terverifikasi',
            self::Ditolak            => 'Ditolak',
        };
    }
}
