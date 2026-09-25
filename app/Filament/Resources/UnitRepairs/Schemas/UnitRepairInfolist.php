<?php

namespace App\Filament\Resources\UnitRepairs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UnitRepairInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('unit.id')
                    ->label('Unit'),
                TextEntry::make('repairType.name')
                    ->label('Repair type'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('override_price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
