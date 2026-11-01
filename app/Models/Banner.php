<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Model Banner — Slider Promosi Beranda EiraStore
 *
 * Mengelola gambar slider promosi, teks judul/subjudul, urutan penayangan,
 * serta status aktif penayangan di halaman depan toko.
 *
 * @property int $id
 * @property string $judul
 * @property string|null $subjudul
 * @property string $gambar
 * @property int $urutan
 * @property bool $is_aktif
 */
class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'subjudul',
        'gambar',
        'urutan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /* ── Scopes ─────────────────────────────────────────── */

    /**
     * Hanya banner yang aktif, diurutkan berdasarkan kolom `urutan`.
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true)->orderBy('urutan');
    }

    /**
     * Urutkan berdasarkan kolom `urutan` ascending.
     */
    public function scopeUrut($query)
    {
        return $query->orderBy('urutan');
    }

    /* ── Accessor ────────────────────────────────────────── */

    /**
     * URL publik gambar banner.
     */
    public function getGambarUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->gambar);
    }
}
