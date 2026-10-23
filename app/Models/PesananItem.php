<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananItem extends Model
{
    protected $table = 'pesanan_item';

    protected $fillable = [
        'pesanan_id',
        'layanan_item_id',
        'peran',            // awal, tujuan, dipilih, tambahan
        'nilai',
        'nama_item',        // salinan nama saat dipesan
        'harga_satuan',     // salinan harga saat dipesan
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Pesanan pemilik item ini */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /** Item layanan asli (bisa NULL jika item sudah dihapus) */
    public function layananItem(): BelongsTo
    {
        return $this->belongsTo(LayananItem::class, 'layanan_item_id');
    }
}
