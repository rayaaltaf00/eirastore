<?php

namespace App\Enums;

/**
 * Role pengguna di sistem EiraStore.
 * Dipakai di kolom users.role
 */
enum Role: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case Worker = 'worker';

    /** Label tampilan untuk UI */
    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Admin => 'Admin',
            self::Worker => 'Worker',
        };
    }
}
