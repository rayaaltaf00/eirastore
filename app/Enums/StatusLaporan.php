<?php

namespace App\Enums;

/**
 * Enum StatusLaporan — Status Verifikasi Bukti Pengerjaan Worker
 *
 * Mengontrol verifikasi bukti tangkapan layar (screenshot) penyelesaian pekerjaan yang dikirim oleh Worker:
 * - MenungguVerifikasi: Laporan telah dikirim oleh Worker dan menunggu peninjauan Admin / Pelanggan.
 * - Terverifikasi: Bukti pengerjaan dinyatakan valid dan sesuai target pesanan.
 * - Ditolak: Bukti tidak sesuai atau belum mencapai target, Worker wajib melakukan revisi.
 */
enum StatusLaporan: string
{
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Terverifikasi = 'terverifikasi';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Terverifikasi => 'Terverifikasi',
            self::Ditolak => 'Ditolak',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            self::Terverifikasi => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            self::Ditolak => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        };
    }
}
