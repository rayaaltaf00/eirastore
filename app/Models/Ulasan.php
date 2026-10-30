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
     * Customer yang memberi ulasan lewat pesanan.
     */
    public function customer(): ?User
    {
        return $this->pesanan?->customer;
    }

    public function getCustomerAttribute(): ?User
    {
        return $this->customer();
    }
}
