<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ulasan extends Model
{
    protected $table = 'ulasan';

    protected $fillable = [
        'pesanan_id',
        'rating',       // 1 sampai 5
        'komentar',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Pesanan yang diulas */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * Customer yang memberi ulasan (lewat pesanan).
     * Shortcut supaya tidak perlu $ulasan->pesanan->customer
     */
    public function customer(): BelongsTo
    {
        return $this->pesanan->customer();
    }
}
