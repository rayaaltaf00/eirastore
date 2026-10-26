<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\StatusPesanan;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'password',
        'role',
        'status_akun',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',       // otomatis hash saat diisi
            'role' => Role::class,         // cast ke enum Role
        ];
    }

    /** Accessor kompatibilitas name -> nama */
    public function getNameAttribute(): string
    {
        return $this->nama ?? '';
    }

    // ── Relasi ──────────────────────────────────────────

    /** Pesanan yang dibuat customer ini */
    public function pesananSebagaiCustomer(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'customer_id');
    }

    /** Pesanan yang ditugaskan ke worker ini */
    public function pesananSebagaiWorker(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'worker_id');
    }

    /** Laporan penyelesaian yang dibuat worker ini */
    public function laporanPenyelesaian(): HasMany
    {
        return $this->hasMany(LaporanPenyelesaian::class, 'worker_id');
    }

    // ── Helper ──────────────────────────────────────────

    /** Cek apakah akun aktif */
    public function isAktif(): bool
    {
        return $this->status_akun === 'aktif';
    }

    /** Cek role */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isCustomer(): bool
    {
        return $this->role === Role::Customer;
    }

    public function isWorker(): bool
    {
        return $this->role === Role::Worker;
    }

    /** Status pesanan yang dianggap masih aktif dikerjakan oleh worker */
    public const STATUS_PESANAN_AKTIF_WORKER = [
        StatusPesanan::Diproses,
        StatusPesanan::SedangDikerjakan,
        StatusPesanan::LaporanMasuk,
        StatusPesanan::PerluRevisiHasil,
        StatusPesanan::WorkerSelesai,
        StatusPesanan::MenungguKonfirmasi,
        StatusPesanan::Dikomplain,
    ];

    /** Hitung jumlah pesanan aktif yang sedang ditangani worker */
    public function getJumlahPesananAktifAttribute(): int
    {
        return $this->pesananSebagaiWorker()
            ->whereIn('status', self::STATUS_PESANAN_AKTIF_WORKER)
            ->count();
    }

    /** Cek apakah worker memiliki pesanan yang masih berjalan */
    public function memilikiPesananAktif(): bool
    {
        return $this->jumlah_pesanan_aktif > 0;
    }

    /** Scope untuk query hanya role Worker */
    public function scopeWorker($query)
    {
        return $query->where('role', Role::Worker);
    }
}
