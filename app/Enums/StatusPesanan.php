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
    case PerluRevisiData = 'perlu_revisi_data';
    case SiapDitugaskan = 'siap_ditugaskan';

    // Pengerjaan
    case Diproses = 'diproses';
    case SedangDikerjakan = 'sedang_dikerjakan';
    case LaporanMasuk = 'laporan_masuk';
    case PerluRevisiHasil = 'perlu_revisi_hasil';
    case WorkerSelesai = 'worker_selesai';

    // Penyelesaian
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case Dikomplain = 'dikomplain';
    case HasilDisetujui = 'hasil_disetujui';
    case Selesai = 'selesai';

    // Terminal (tidak bisa berubah lagi)
    case Dibatalkan = 'dibatalkan';
    case Kedaluwarsa = 'kedaluwarsa';

    /** Label tampilan untuk UI */
    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::PerluRevisiData => 'Perlu Revisi Data',
            self::SiapDitugaskan => 'Siap Ditugaskan',
            self::Diproses => 'Diproses',
            self::SedangDikerjakan => 'Sedang Dikerjakan',
            self::LaporanMasuk => 'Laporan Masuk',
            self::PerluRevisiHasil => 'Perlu Revisi Hasil',
            self::WorkerSelesai => 'Worker Selesai',
            self::MenungguKonfirmasi => 'Menunggu Konfirmasi',
            self::Dikomplain => 'Dikomplain',
            self::HasilDisetujui => 'Hasil Disetujui',
            self::Dibatalkan => 'Dibatalkan',
            self::Kedaluwarsa => 'Kedaluwarsa',
            self::Selesai => 'Selesai',
        };
    }

    /** Warna badge untuk tampilan (Tailwind CSS classes lengkap: bg, text, border) */
    public function badgeColor(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            self::MenungguVerifikasi => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            self::PerluRevisiData => 'bg-orange-500/10 text-orange-400 border-orange-500/30',
            self::SiapDitugaskan => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
            self::Diproses => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
            self::SedangDikerjakan => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
            self::LaporanMasuk => 'bg-teal-500/10 text-teal-400 border-teal-500/30',
            self::PerluRevisiHasil => 'bg-orange-500/10 text-orange-400 border-orange-500/30',
            self::WorkerSelesai => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            self::MenungguKonfirmasi => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
            self::Dikomplain => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
            self::HasilDisetujui => 'bg-green-500/10 text-green-400 border-green-500/30',
            self::Dibatalkan => 'bg-slate-500/10 text-slate-400 border-slate-500/30',
            self::Kedaluwarsa => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/30',
            self::Selesai => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        };
    }

    /** Alias warna badge untuk kompatibilitas */
    public function warnaBadge(): string
    {
        return $this->badgeColor();
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
