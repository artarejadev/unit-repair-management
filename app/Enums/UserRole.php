<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case TEKNISI = 'TEKNISI';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::TEKNISI => 'Teknisi',
        };
    }
}