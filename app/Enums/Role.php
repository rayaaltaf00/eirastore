<?php

namespace App\Enums;

/**
 * Enum Role — Tingkatan Peran Pengguna di Sistem EiraStore
 *
 * Mendefinisikan 3 tingkat hak akses utama:
 * - Customer: Pelanggan yang memesan jasa joki, melakukan pembayaran, memantau pesanan, dan memberi ulasan.
 * - Worker: Penjoki mitra yang ditugaskan mengerjakan pesanan dan mengunggah bukti hasil penyelesaian.
 * - Admin: Pengelola toko yang memverifikasi transaksi, menugaskan worker, dan mengelola katalog layanan.
 */
enum Role: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case Worker = 'worker';

    /** Label tampilan untuk UI */
    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Admin => 'Admin',
            self::Worker => 'Worker',
        };
    }
}
