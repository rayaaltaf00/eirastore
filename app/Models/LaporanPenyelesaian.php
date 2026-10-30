<?php

namespace App\Models;

use App\Enums\StatusLaporan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanPenyelesaian extends Model
{
    protected $table = 'laporan_penyelesaian';

    protected $fillable = [
        'pesanan_id',
        'worker_id',
        'foto_bukti',           // path file di storage privat
        'catatan',              // catatan opsional dari worker
        'status',
        'diverifikasi_oleh',
        'diverifikasi_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusLaporan::class,
            'diverifikasi_at' => 'datetime',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Pesanan yang dilaporkan */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /** Worker yang membuat laporan */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /** Admin yang memverifikasi */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
