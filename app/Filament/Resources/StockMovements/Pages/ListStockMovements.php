<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\Sparepart;
use App\Services\Stock\StockMovementService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('stockIn')
                ->label('Stok Masuk')
                ->icon('heroicon-o-plus-circle')
                ->schema([
                    Select::make('sparepart_id')
                        ->label('Sparepart')
                        ->options(
                            fn () => Sparepart::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->required(),

                    TextInput::make('reference')
                        ->label('Referensi')
                        ->maxLength(255)
                        ->nullable(),

                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $sparepart = Sparepart::query()
                        ->findOrFail($data['sparepart_id']);

                    app(StockMovementService::class)->in(
                        sparepart: $sparepart,
                        quantity: (int) $data['quantity'],
                        reference: $data['reference'] ?? null,
                        notes: $data['notes'] ?? null,
                        user: auth()->user(),
                    );

                    Notification::make()
                        ->title('Stok berhasil ditambahkan')
                        ->success()
                        ->send();
                }),
        ];
    }
}