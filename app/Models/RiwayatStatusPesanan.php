<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusPesanan extends Model
{
    protected $table = 'riwayat_status_pesanan';

    public $timestamps = false; // hanya pakai created_at manual

    protected $fillable = [
        'pesanan_id',
        'status_lama',
        'status_baru',
        'catatan',
        'diubah_oleh',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // Otomatis isi created_at saat membuat record baru
    protected static function booted(): void
    {
        static::creating(function ($riwayat) {
            $riwayat->created_at = $riwayat->created_at ?? now();
        });
    }

    // ── Relasi ──────────────────────────────────────────

    /** Pesanan yang berubah status */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /** User yang mengubah status (NULL = sistem/scheduler) */
    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
