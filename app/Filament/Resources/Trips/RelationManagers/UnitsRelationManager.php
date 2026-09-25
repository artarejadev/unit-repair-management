<?php

namespace App\Filament\Resources\Trips\RelationManagers;

use App\Enums\UserRole;
use App\Models\Unit;
use App\Models\UnitAssignment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Filament\Resources\Units\UnitResource;

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
                    ->required()
                    ->rows(3)
                    ->helperText('Masukkan satu IMEI untuk satu unit.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn (Unit $record): string => UnitResource::getUrl('view', [
                    'record' => $record,
                ])
            )
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

                TextColumn::make('currentAssignment.technician.name')
                    ->label('Teknisi')
                    ->placeholder('Belum di-assign')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])

            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Unit')
                    ->mutateFormDataUsing(function (array $data): array {
                        $trip = $this->getOwnerRecord();

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
                    ->schema([
                        Textarea::make('imeis')
                            ->label('Daftar IMEI')
                            ->rows(12)
                            ->required()
                            ->placeholder(
                                "123456789012345\n123456789012346\n123456789012347"
                            )
                            ->helperText('Satu baris = satu unit.'),
                    ])
                    ->action(function (array $data): void {
                        $trip = $this->getOwnerRecord();

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

                        $duplicates = $imeis
                            ->duplicates()
                            ->unique()
                            ->values();

                        if ($duplicates->isNotEmpty()) {
                            throw ValidationException::withMessages([
                                'imeis' => 'Ada IMEI duplikat: ' .
                                    $duplicates->implode(', '),
                            ]);
                        }

                        $existing = Unit::query()
                            ->whereIn('imei', $imeis->all())
                            ->pluck('imei');

                        if ($existing->isNotEmpty()) {
                            throw ValidationException::withMessages([
                                'imeis' => 'IMEI sudah terdaftar: ' .
                                    $existing->implode(', '),
                            ]);
                        }

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

                        Notification::make()
                            ->title('Unit berhasil ditambahkan')
                            ->body($imeis->count() . ' unit berhasil dibuat.')
                            ->success()
                            ->send();
                    }),
            ])

            ->recordActions([
                EditAction::make()
                ->url(
                    fn (Unit $record): string => UnitResource::getUrl('edit', [
                        'record' => $record,
                    ])
                ),
                DeleteAction::make(),

                Action::make('assignTechnician')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Select::make('technician_id')
                            ->label('Teknisi')
                            ->options(
                                fn () => User::query()
                                    ->where('role', UserRole::TEKNISI->value)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->action(function (Unit $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            UnitAssignment::query()
                                ->where('unit_id', $record->id)
                                ->whereNull('ended_at')
                                ->update([
                                    'ended_at' => now(),
                                ]);

                            UnitAssignment::create([
                                'unit_id' => $record->id,
                                'technician_id' => $data['technician_id'],
                                'assigned_at' => now(),
                                'ended_at' => null,
                                'notes' => $data['notes'] ?? null,
                            ]);
                        });

                        Notification::make()
                            ->title('Teknisi berhasil di-assign')
                            ->success()
                            ->send();
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignTechnician')
                        ->label('Assign Teknisi')
                        ->icon('heroicon-o-user-plus')
                        ->schema([
                            Select::make('technician_id')
                                ->label('Teknisi')
                                ->options(
                                    fn () => User::query()
                                        ->where(
                                            'role',
                                            UserRole::TEKNISI->value
                                        )
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->required(),

                            Textarea::make('notes')
                                ->label('Catatan')
                                ->rows(3)
                                ->nullable(),
                        ])
                        ->requiresConfirmation()
                        ->action(function (
                            Collection $records,
                            array $data
                        ): void {
                            DB::transaction(function () use (
                                $records,
                                $data
                            ): void {
                                foreach ($records as $unit) {
                                    UnitAssignment::query()
                                        ->where('unit_id', $unit->id)
                                        ->whereNull('ended_at')
                                        ->update([
                                            'ended_at' => now(),
                                        ]);

                                    UnitAssignment::create([
                                        'unit_id' => $unit->id,
                                        'technician_id' => $data['technician_id'],
                                        'assigned_at' => now(),
                                        'ended_at' => null,
                                        'notes' => $data['notes'] ?? null,
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Assignment berhasil')
                                ->body(
                                    $records->count() .
                                    ' unit berhasil di-assign.'
                                )
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}