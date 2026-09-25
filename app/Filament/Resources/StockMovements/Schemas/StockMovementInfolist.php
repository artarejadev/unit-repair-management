<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('sparepart.name')
                    ->label('Sparepart'),
                TextEntry::make('unit_id')
                    ->placeholder('-'),
                TextEntry::make('unit_sparepart_id')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('movement_type'),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('before_qty')
                    ->numeric(),
                TextEntry::make('after_qty')
                    ->numeric(),
                TextEntry::make('reference')
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
