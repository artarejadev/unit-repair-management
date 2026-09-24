<?php

namespace App\Filament\Resources\Trips\RelationManagers;

use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Unit';

    protected static ?string $recordTitleAttribute = 'imei';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('imei')
                    ->label('IMEI')
                    ->rows(3)
                    ->required()
                    ->helperText('Masukkan satu IMEI untuk setiap unit.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('imei')
                    ->label('IMEI')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Unit')
                    ->mutateFormDataUsing(function (array $data, $livewire): array {
                        $trip = $livewire->getOwnerRecord();

                        return [
                            'imei' => trim($data['imei']),
                            'customer_id' => $trip->customer_id,
                            'trip_id' => $trip->id,
                            'status' => 'PENDING',
                        ];
                    }),

                Action::make('bulkCreate')
                    ->label('Bulk IMEI')
                    ->icon('heroicon-o-queue-list')
                    ->modalHeading('Tambah Banyak Unit')
                    ->schema([
                        Textarea::make('imeis')
                            ->label('Daftar IMEI')
                            ->rows(12)
                            ->required()
                            ->placeholder(
                                "Masukkan satu IMEI per baris\n\n123456789012345\n123456789012346\n123456789012347"
                            )
                            ->helperText(
                                'Satu baris = satu unit.'
                            ),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $trip = $livewire->getOwnerRecord();

                        $imeis = preg_split(
                            '/\R/',
                            $data['imeis'],
                            -1,
                            PREG_SPLIT_NO_EMPTY
                        );

                        $imeis = collect($imeis)
                            ->map(fn ($imei) => trim($imei))
                            ->filter()
                            ->values();

                        if ($imeis->isEmpty()) {
                            throw ValidationException::withMessages([
                                'imeis' => 'Tidak ada IMEI yang dapat diproses.',
                            ]);
                        }

                        $duplicatesInInput = $imeis
                            ->duplicates()
                            ->unique()
                            ->values();

                        if ($duplicatesInInput->isNotEmpty()) {
                            throw ValidationException::withMessages([
                                'imeis' => 'Ada IMEI duplikat dalam input: ' .
                                    $duplicatesInInput->implode(', '),
                            ]);
                        }

                        $existingImeis = Unit::query()
                            ->whereIn('imei', $imeis->all())
                            ->pluck('imei');

                        if ($existingImeis->isNotEmpty()) {
                            throw ValidationException::withMessages([
                                'imeis' => 'IMEI berikut sudah terdaftar: ' .
                                    $existingImeis->implode(', '),
                            ]);
                        }

                        try {
                            DB::transaction(function () use ($imeis, $trip): void {
                                foreach ($imeis as $imei) {
                                    Unit::create([
                                        'customer_id' => $trip->customer_id,
                                        'trip_id' => $trip->id,
                                        'imei' => $imei,
                                        'status' => 'PENDING',
                                    ]);
                                }
                            });
                        } catch (QueryException $exception) {
                            throw ValidationException::withMessages([
                                'imeis' => 'Sebagian data tidak dapat disimpan. Periksa kembali IMEI dan coba lagi.',
                            ]);
                        }

                        Notification::make()
                            ->title('Unit berhasil ditambahkan')
                            ->body($imeis->count() . ' unit berhasil masuk ke Trip.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}