<?php

namespace App\Filament\Resources\Trips\Schemas;

use App\Models\Customer;
use App\Models\Trip;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class TripForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(
                        fn (?Trip $record): bool =>
                            $record?->units()->exists() ?? false
                    )
                    ->helperText(
                        fn (?Trip $record): ?string =>
                            $record?->units()->exists()
                                ? 'Customer tidak dapat diubah karena Trip sudah memiliki Unit.'
                                : null
                    ),

                TextInput::make('trip_number')
                    ->label('Nomor Trip')
                    ->required()
                    ->maxLength(50)
                    ->unique(
                        table: Trip::class,
                        column: 'trip_number',
                        ignoreRecord: true,
                    )
                    ->placeholder('Contoh: TRIP-001'),

                DatePicker::make('trip_date')
                    ->label('Tanggal Trip')
                    ->required()
                    ->default(now()),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}