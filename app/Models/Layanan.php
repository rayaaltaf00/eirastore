<?php

namespace App\Models;

use App\Enums\TipeHarga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    protected $table = 'layanan'; // nama tabel tidak mengikuti konvensi plural Laravel

    protected $fillable = [
        'game_id',
        'nama_layanan',
        'deskripsi',
        'tipe_harga',
        'estimasi_waktu',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tipe_harga' => TipeHarga::class,
            'aktif' => 'boolean',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Game pemilik layanan ini */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /** Item-item milik layanan ini */
    public function items(): HasMany
    {
        return $this->hasMany(LayananItem::class, 'layanan_id');
    }

    /** Pesanan yang memakai layanan ini */
    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'layanan_id');
    }

    // ── Scope ───────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
