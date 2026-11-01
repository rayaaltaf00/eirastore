<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model LayananItem — Butir Rincian / Tingkatan Layanan Joki
 *
 * Mengelola komponen harga:
 * - Utama: Tingkatan rank (tangga), persentase progres, atau opsi dasar.
 * - Tambahan: Add-on pelengkap (misal: Live Streaming, Request Hero, Waktu Cepat).
 * - Hierarki: Relasi parent/child untuk pengelompokan wilayah/region peta.
 *
 * @property int $id
 * @property int $layanan_id
 * @property int|null $parent_id
 * @property string $jenis
 * @property string|null $kategori
 * @property string $nama
 * @property int $urutan
 * @property float $harga
 * @property int|null $nilai_maks
 * @property string|null $satuan
 * @property bool $aktif
 */
class LayananItem extends Model
{
    protected $table = 'layanan_item';

    protected $fillable = [
        'layanan_id',
        'parent_id',
        'jenis',
        'kategori',
        'nama',
        'urutan',
        'harga',
        'nilai_maks',
        'satuan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'nilai_maks' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Layanan pemilik item ini */
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    /** Item induk (untuk hierarki, misal: region → wilayah) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Item anak (wilayah di bawah region) */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // ── Scope ───────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /** Hanya item utama (bukan tambahan) */
    public function scopeUtama($query)
    {
        return $query->where('jenis', 'utama');
    }

    /** Hanya item tambahan */
    public function scopeTambahan($query)
    {
        return $query->where('jenis', 'tambahan');
    }

    /** Urutkan berdasarkan kolom urutan */
    public function scopeUrut($query)
    {
        return $query->orderBy('urutan');
    }
}
