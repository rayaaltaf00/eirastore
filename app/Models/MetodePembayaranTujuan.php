<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MetodePembayaranTujuan extends Model
{
    protected $table = 'metode_pembayaran_tujuan';

    protected $fillable = [
        'nama_metode',
        'kategori',
        'nomor_rekening',
        'atas_nama',
        'logo',
        'gambar_qris',
        'petunjuk_transfer',
        'urutan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /** Scope untuk menampilkan rekening yang aktif dan berurutan */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true)->orderBy('urutan');
    }

    /** Cek apakah metode ini bertipe QRIS */
    public function isQris(): bool
    {
        return strtolower($this->kategori) === 'qris';
    }

    /** URL gambar logo */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        return Storage::disk('public')->url($this->logo);
    }

    /** URL gambar QRIS */
    public function getGambarQrisUrlAttribute(): ?string
    {
        if (! $this->gambar_qris) {
            return null;
        }

        if (str_starts_with($this->gambar_qris, 'http://') || str_starts_with($this->gambar_qris, 'https://')) {
            return $this->gambar_qris;
        }

        return Storage::disk('public')->url($this->gambar_qris);
    }
}
