<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'nama_game',
        'kategori',
        'deskripsi',
        'gambar',
        'aktif',
    ];

    protected $appends = ['gambar_url'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    /** URL gambar fleksibel (mendukung URL eksternal atau file lokal di storage) */
    public function getGambarUrlAttribute(): string
    {
        if (empty($this->gambar)) {
            return asset('images/game-placeholder.jpg');
        }
        if (str_starts_with($this->gambar, 'http://') || str_starts_with($this->gambar, 'https://')) {
            return $this->gambar;
        }
        return asset('storage/' . $this->gambar);
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
