<?php

namespace App\Filament\Resources\RepairTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RepairTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Repair')
                    ->placeholder('Contoh: Ganti LCD')
                    ->required()
                    ->maxLength(255),

                TextInput::make('default_price')
                    ->label('Harga Default')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->step(1)
                    ->required()
                    ->helperText(
                        'Harga ini menjadi acuan saat jenis repair ditambahkan ke unit.'
                    ),
            ]);
    }
}