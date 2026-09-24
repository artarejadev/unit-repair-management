<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Customer')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Customer'),

                        TextEntry::make('phone')
                            ->label('Nomor Telepon')
                            ->placeholder('-'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('-'),

                        TextEntry::make('address')
                            ->label('Alamat')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Riwayat Trip')
                    ->schema([
                        RepeatableEntry::make('trips')
                            ->label('')
                            ->table([
                                RepeatableEntry\TableColumn::make('Nomor Trip'),
                                RepeatableEntry\TableColumn::make('Tanggal'),
                                RepeatableEntry\TableColumn::make('Catatan'),
                            ])
                            ->schema([
                                TextEntry::make('trip_number')
                                    ->label('Nomor Trip'),

                                TextEntry::make('trip_date')
                                    ->label('Tanggal')
                                    ->date('d M Y'),

                                TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('-'),
                            ])
                            ->contained(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}