<?php

namespace App\Enums;

enum StockMovementType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
    case RETURN = 'RETURN';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Stok Masuk',
            self::OUT => 'Stok Keluar',
            self::RETURN => 'Pengembalian',
        };
    }

    public function isIncrease(): bool
    {
        return match ($this) {
            self::IN,
            self::RETURN => true,

            self::OUT => false,
        };
    }
}