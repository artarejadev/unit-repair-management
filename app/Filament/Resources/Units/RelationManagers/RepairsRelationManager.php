<?php

namespace App\Filament\Resources\Units\RelationManagers;

use App\Models\UnitRepair;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

use Filament\Forms\Components\TextInput;
use App\Filament\Resources\UnitRepairs\UnitRepairResource;

class RepairsRelationManager extends RelationManager
{
    protected static string $relationship = 'repairs';

    protected static ?string $title = 'Repair';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('repair_type_id')
                    ->label('Jenis Repair')
                    ->relationship(
                        name: 'repairType',
                        titleAttribute: 'name',
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('override_price')
                    ->label('Override Harga')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->step(1)
                    ->nullable()
                    ->visible(
                        fn (): bool => auth()->user()?->isAdmin() ?? false
                    )
                    ->helperText(
                        'Kosongkan jika menggunakan harga snapshot.'
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn ($record) => UnitRepairResource::getUrl('view', [
                    'record' => $record,
                ])
            )
            ->columns([
                TextColumn::make('repairType.name')
                    ->label('Jenis Repair')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Harga Snapshot')
                    ->formatStateUsing(
                        fn ($state): string => 'Rp ' .
                            number_format(
                                (float) $state,
                                0,
                                ',',
                                '.'
                            )
                    ),

                TextColumn::make('override_price')
                    ->label('Override Harga')
                    ->placeholder('-')
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? 'Rp ' . number_format(
                                (float) $state,
                                0,
                                ',',
                                '.'
                            )
                            : '-'
                    ),

                TextColumn::make('final_price')
                    ->label('Harga Final')
                    ->getStateUsing(
                        fn (UnitRepair $record): float => $record->final_price
                    )
                    ->formatStateUsing(
                        fn ($state): string => 'Rp ' .
                            number_format(
                                (float) $state,
                                0,
                                ',',
                                '.'
                            )
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Repair')
                    ->mutateFormDataUsing(function (array $data): array {
                        return [
                            'repair_type_id' => $data['repair_type_id'],
                            'override_price' => null,
                        ];
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->url(
                        fn ($record) => UnitRepairResource::getUrl('edit', [
                            'record' => $record,
                        ])
                    ),
                DeleteAction::make(),
            ]);
    }
}