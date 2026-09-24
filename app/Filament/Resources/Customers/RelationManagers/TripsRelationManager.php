<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\Trip;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TripsRelationManager extends RelationManager
{
    protected static string $relationship = 'trips';

    protected static ?string $title = 'Trip Customer';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('trip_date')
                ->label('Tanggal Trip')
                ->required(),

            TextInput::make('trip_number')
                ->label('Nomor Trip')
                ->maxLength(100)
                ->nullable(),

            TextInput::make('notes')
                ->label('Catatan')
                ->maxLength(1000)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('trip_date', 'desc')
            ->columns([
                TextColumn::make('trip_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('trip_number')
                    ->label('Nomor Trip')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('units_count')
                    ->label('Unit')
                    ->counts('units')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Trip'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}