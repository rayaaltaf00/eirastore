<?php

namespace App\Enums;

/**
 * Status pembayaran.
 * Dipakai di kolom pembayaran.status
 */
enum StatusPembayaran: string
{
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            self::Disetujui => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            self::Ditolak => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        };
    }
}
