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
        'gambar',
        'deskripsi',
        'tipe_harga',
        'estimasi_waktu',
        'aktif',
    ];

    protected $appends = ['gambar_url'];

    protected function casts(): array
    {
        return [
            'tipe_harga' => TipeHarga::class,
            'aktif' => 'boolean',
        ];
    }

    /** URL poster layanan joki (fleksibel: URL eksternal, storage lokal, atau fallback ke gambar game) */
    public function getGambarUrlAttribute(): string
    {
        if (! empty($this->gambar)) {
            if (str_starts_with($this->gambar, 'http://') || str_starts_with($this->gambar, 'https://')) {
                return $this->gambar;
            }

            return asset('storage/'.$this->gambar);
        }

        // Fallback ke gambar game pemilik jika layanan belum memiliki poster tersendiri
        if ($this->game?->gambar_url) {
            return $this->game->gambar_url;
        }

        return asset('images/game-placeholder.jpg');
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
