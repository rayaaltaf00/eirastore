<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'pesanan_id',
        'metode_pembayaran',
        'bukti_transfer',       // path file di storage privat
        'status',
        'diverifikasi_oleh',
        'diverifikasi_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPembayaran::class,
            'diverifikasi_at' => 'datetime',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Pesanan yang dibayar */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /** Admin yang memverifikasi */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
