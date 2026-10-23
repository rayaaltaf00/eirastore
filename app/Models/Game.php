<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'nama_game',
        'deskripsi',
        'gambar',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Layanan joki milik game ini */
    public function layanan(): HasMany
    {
        return $this->hasMany(Layanan::class, 'game_id');
    }

    // ── Scope ───────────────────────────────────────────

    /** Hanya game yang aktif (untuk katalog publik) */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
