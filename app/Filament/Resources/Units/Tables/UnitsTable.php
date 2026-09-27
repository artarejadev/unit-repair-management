<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Enums\UnitStatus;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('imei')
                    ->label('IMEI')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (UnitStatus $state) => match ($state) {
                            UnitStatus::PENDING => 'Pending',
                            UnitStatus::PROSES => 'Proses',
                            UnitStatus::SELESAI => 'Selesai',
                            UnitStatus::DITAGIHKAN => 'Ditagihkan',
                            UnitStatus::DIAMBIL => 'Sudah Diambil',
                            UnitStatus::REWORK => 'Rework',
                        }
                    ),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trip.trip_date')
                    ->label('Tanggal Trip')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}