<?php

namespace App\Models;

use App\Enums\Role;
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
}
