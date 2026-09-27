<?php

namespace App\Enums;

enum UnitStatus: string
{
    case PENDING = 'PENDING';
    case PROSES = 'PROSES';
    case SELESAI = 'SELESAI';
    case DITAGIHKAN = 'DITAGIHKAN';
    case DIAMBIL = 'DIAMBIL';
    case REWORK = 'REWORK';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROSES => 'Proses',
            self::SELESAI => 'Selesai',
            self::DITAGIHKAN => 'Ditagihkan',
            self::DIAMBIL => 'Diambil',
            self::REWORK => 'Rework',
        };
    }
}