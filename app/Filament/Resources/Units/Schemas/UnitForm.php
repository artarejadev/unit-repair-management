<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Enums\UnitStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('imei')
                    ->label('IMEI')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                Select::make('status')
                    ->label('Status')
                    ->options(UnitStatus::class)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(
                        'Status dikelola melalui alur operasional unit.'
                    ),
            ]);
    }
}