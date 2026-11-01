<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model RiwayatStatusPesanan — Jejak Audit (Audit Trail / Linimasa Pesanan)
 *
 * Mencatat setiap perubahan status pesanan secara kronologis:
 * status awal, status baru, catatan alasan perubahan, pelaku perubahan (Admin, Worker, atau NULL untuk Scheduler sistem),
 * serta stempel waktu (created_at).
 *
 * @property int $id
 * @property int $pesanan_id
 * @property string $status_lama
 * @property string $status_baru
 * @property string|null $catatan
 * @property int|null $diubah_oleh
 * @property Carbon $created_at
 */
class RiwayatStatusPesanan extends Model
{
    protected $table = 'riwayat_status_pesanan';

    public $timestamps = false; // hanya pakai created_at manual

    protected $fillable = [
        'pesanan_id',
        'status_lama',
        'status_baru',
        'catatan',
        'diubah_oleh',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Mutator agar dapat menerima enum StatusPesanan maupun string
     */
    public function setStatusLamaAttribute($value): void
    {
        $this->attributes['status_lama'] = $value instanceof \BackedEnum ? $value->value : $value;
    }

    /**
     * Mutator agar dapat menerima enum StatusPesanan maupun string
     */
    public function setStatusBaruAttribute($value): void
    {
        $this->attributes['status_baru'] = $value instanceof \BackedEnum ? $value->value : $value;
    }

    /**
     * Accessor untuk mendapatkan Enum StatusPesanan dari status_lama
     */
    public function getStatusLamaEnumAttribute(): ?StatusPesanan
    {
        return $this->status_lama ? StatusPesanan::tryFrom($this->status_lama) : null;
    }

    /**
     * Accessor untuk mendapatkan Enum StatusPesanan dari status_baru
     */
    public function getStatusBaruEnumAttribute(): ?StatusPesanan
    {
        return $this->status_baru ? StatusPesanan::tryFrom($this->status_baru) : null;
    }

    // Otomatis isi created_at saat membuat record baru
    protected static function booted(): void
    {
        static::creating(function ($riwayat) {
            $riwayat->created_at = $riwayat->created_at ?? now();
        });
    }

    // ── Relasi ──────────────────────────────────────────

    /** Pesanan yang berubah status */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /** User yang mengubah status (NULL = sistem/scheduler) */
    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
