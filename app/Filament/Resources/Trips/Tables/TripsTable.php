<?php

namespace App\Filament\Resources\Trips\Tables;

use App\Enums\UnitStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TripsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trip_number')
                    ->label('Nomor Trip')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trip_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('units_count')
                    ->label('Total Unit')
                    ->sortable(),

                TextColumn::make('pending_units_count')
                    ->label('Pending')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('proses_units_count')
                    ->label('Proses')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('selesai_units_count')
                    ->label('Selesai')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('ditagihkan_units_count')
                    ->label('Ditagihkan')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('diambil_units_count')
                    ->label('Diambil')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('unit_status')
                    ->label('Status Unit')
                    ->options([
                        UnitStatus::PENDING->value => UnitStatus::PENDING->label(),
                        UnitStatus::PROSES->value => UnitStatus::PROSES->label(),
                        UnitStatus::SELESAI->value => UnitStatus::SELESAI->label(),
                        UnitStatus::DITAGIHKAN->value => UnitStatus::DITAGIHKAN->label(),
                        UnitStatus::DIAMBIL->value => UnitStatus::DIAMBIL->label(),
                    ])
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'units',
                            fn ($unitQuery) =>
                                $unitQuery->where(
                                    'status',
                                    $data['value']
                                )
                        );
                    }),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->defaultSort('trip_date', 'desc');
    }
}