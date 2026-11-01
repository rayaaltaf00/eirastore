<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Game — Master Data Judul Game
 *
 * Mengelola katalog game yang didukung EiraStore (misal: MLBB, Genshin Impact, Valorant),
 * beserta opsi login akun game dinamis, kategori, dan relasi ke daftar layanan joki.
 *
 * @property int $id
 * @property string $nama_game
 * @property string|null $kategori
 * @property string|null $deskripsi
 * @property string|null $gambar
 * @property bool $aktif
 */
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

        return asset('storage/'.$this->gambar);
    }

    /** Daftar metode login akun yang sesuai dengan jenis game */
    public function getOpsiLoginAttribute(): array
    {
        $nama = strtolower($this->nama_game ?? '');

        if (str_contains($nama, 'mobile legends') || str_contains($nama, 'mlbb')) {
            return ['Moonton ID', 'Google Play / Gmail', 'VK', 'TikTok', 'Facebook'];
        }

        if (str_contains($nama, 'genshin') || str_contains($nama, 'honkai') || str_contains($nama, 'star rail')) {
            return ['HoYoverse Account', 'Google', 'Apple ID', 'Facebook'];
        }

        if (str_contains($nama, 'free fire')) {
            return ['Google', 'Facebook', 'VK', 'Twitter / X'];
        }

        if (str_contains($nama, 'valorant') || str_contains($nama, 'riot')) {
            return ['Riot Games ID'];
        }

        if (str_contains($nama, 'pubg')) {
            return ['Twitter / X', 'Facebook', 'Google Play', 'Apple ID'];
        }

        if (str_contains($nama, 'honor of kings') || str_contains($nama, 'hok')) {
            return ['Level Infinite Pass', 'Google', 'Facebook'];
        }

        return ['Email / Akun Resmi', 'Google', 'Apple ID', 'Facebook', 'Lainnya'];
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
