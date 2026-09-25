<?php

namespace App\Filament\Resources\UnitRepairs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitRepairForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('repair_type_name')
                ->label('Jenis Repair')
                ->formatStateUsing(
                    fn ($record) => $record?->repairType?->name
                )
                ->disabled()
                ->dehydrated(false),

            TextInput::make('price')
                ->label('Harga Snapshot')
                ->prefix('Rp')
                ->numeric()
                ->disabled()
                ->dehydrated(false),

            TextInput::make('override_price')
                ->label('Override Harga')
                ->prefix('Rp')
                ->numeric()
                ->minValue(0)
                ->nullable()
                ->helperText(
                    'Kosongkan untuk menggunakan harga snapshot.'
                ),
        ]);
    }
}