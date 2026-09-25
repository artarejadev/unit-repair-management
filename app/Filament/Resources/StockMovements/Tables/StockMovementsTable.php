<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Enums\StockMovementType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('sparepart.name')
                    ->label('Sparepart')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('movement_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof \App\Enums\StockMovementType
                            ? $state->label()
                            : $state
                    ),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->sortable(),

                TextColumn::make('before_qty')
                    ->label('Sebelum')
                    ->sortable(),

                TextColumn::make('after_qty')
                    ->label('Sesudah')
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('user.name')
                    ->label('Oleh')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->placeholder('-'),
            ]);
    }
}