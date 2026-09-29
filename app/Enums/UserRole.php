<?php

namespace App\Enums;

enum UserRole: string
{
    case Player = 'player';
    case Admin = 'admin';
    case SuperAdmin = 'superadmin';

    public function isAdministrator(): bool
    {
        return $this === self::Admin || $this === self::SuperAdmin;
    }
}
