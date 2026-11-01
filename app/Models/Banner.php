<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
            'urutan'   => 'integer',
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
