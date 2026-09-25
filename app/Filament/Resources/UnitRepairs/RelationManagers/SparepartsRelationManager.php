<?php

namespace App\Filament\Resources\UnitRepairs\RelationManagers;

use App\Models\Sparepart;
use App\Models\UnitSparepart;
use App\Services\Stock\UnitRepairSparepartService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SparepartsRelationManager extends RelationManager
{
    protected static string $relationship = 'spareparts';

    protected static ?string $title = 'Sparepart yang Digunakan';

    protected static ?string $modelLabel = 'Sparepart';

    protected static ?string $pluralModelLabel = 'Sparepart';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('sparepart_id')
                ->label('Sparepart')
                ->options(
                    fn (): array => Sparepart::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('quantity')
                ->label('Qty')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->default(1)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sparepart_id')
            ->columns([
                TextColumn::make('sparepart.name')
                    ->label('Sparepart')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Digunakan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Sparepart')
                    ->modalHeading('Tambah Sparepart yang Digunakan')
                    ->modalSubmitActionLabel('Simpan')
                    ->using(function (
                        array $data,
                        string $model,
                    ): UnitSparepart {
                        $sparepart = Sparepart::query()
                            ->findOrFail($data['sparepart_id']);

                        return app(UnitRepairSparepartService::class)->use(
                            unitRepair: $this->getOwnerRecord(),
                            sparepart: $sparepart,
                            quantity: (int) $data['quantity'],
                            user: auth()->user(),
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->modalHeading('Edit Sparepart yang Digunakan')
                    ->modalSubmitActionLabel('Simpan')
                    ->using(function (
                        UnitSparepart $record,
                        array $data,
                    ): UnitSparepart {
                        $sparepart = Sparepart::query()
                            ->findOrFail($data['sparepart_id']);

                        return app(UnitRepairSparepartService::class)->update(
                            unitSparepart: $record,
                            sparepart: $sparepart,
                            quantity: (int) $data['quantity'],
                            user: auth()->user(),
                        );
                    }),

                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Sparepart')
                    ->modalDescription(
                        'Sparepart akan dihapus dari repair dan stok akan dikembalikan sesuai jumlah pemakaian.'
                    )
                    ->requiresConfirmation()
                    ->action(function (UnitSparepart $record): void {
                        app(UnitRepairSparepartService::class)->delete(
                            unitSparepart: $record,
                            user: auth()->user(),
                        );
                    }),
            ])
            ->toolbarActions([]);
    }
}