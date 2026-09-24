<?php

namespace App\Filament\Resources\Trips\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TripInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Trip')
                    ->schema([
                        TextEntry::make('trip_number')
                            ->label('Nomor Trip'),

                        TextEntry::make('trip_date')
                            ->label('Tanggal Trip')
                            ->date('d M Y'),

                        TextEntry::make('customer.name')
                            ->label('Customer'),

                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Progress Unit')
                    ->schema([
                        TextEntry::make('units_count')
                            ->label('Total Unit')
                            ->badge(),

                        TextEntry::make('pending_units_count')
                            ->label('Pending')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('proses_units_count')
                            ->label('Proses')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('selesai_units_count')
                            ->label('Selesai')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('ditagihkan_units_count')
                            ->label('Ditagihkan')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('diambil_units_count')
                            ->label('Diambil')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(3),
            ]);
    }
}