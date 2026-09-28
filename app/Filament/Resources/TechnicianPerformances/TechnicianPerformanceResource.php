<?php

namespace App\Filament\Resources\TechnicianPerformances;

use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Models\UnitAssignment;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Models\UnitRepair;
use Illuminate\Database\Eloquent\Builder;

class TechnicianPerformanceResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Performa Teknisi';

    protected static ?string $modelLabel = 'Performa Teknisi';

    protected static ?string $pluralModelLabel = 'Performa Teknisi';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('users.role', UserRole::TEKNISI->value)
            ->select('users.*')

            // Jumlah unit yang pernah ditugaskan ke teknisi.
            ->selectSub(
                UnitAssignment::query()
                    ->selectRaw('COUNT(DISTINCT unit_assignments.unit_id)')
                    ->whereColumn(
                        'unit_assignments.technician_id',
                        'users.id'
                    ),
                'total_units'
            )

            // Unit yang saat ini berstatus SELESAI,
            // dari unit-unit yang pernah ditugaskan ke teknisi.
            ->selectSub(
                UnitAssignment::query()
                    ->selectRaw(
                        'COUNT(DISTINCT unit_assignments.unit_id)'
                    )
                    ->join(
                        'units',
                        'units.id',
                        '=',
                        'unit_assignments.unit_id'
                    )
                    ->whereColumn(
                        'unit_assignments.technician_id',
                        'users.id'
                    )
                    ->where(
                        'units.status',
                        UnitStatus::SELESAI->value
                    ),
                'completed_units'
            )

            // Unit yang saat ini sedang PROSES.
            ->selectSub(
                UnitAssignment::query()
                    ->selectRaw(
                        'COUNT(DISTINCT unit_assignments.unit_id)'
                    )
                    ->join(
                        'units',
                        'units.id',
                        '=',
                        'unit_assignments.unit_id'
                    )
                    ->whereColumn(
                        'unit_assignments.technician_id',
                        'users.id'
                    )
                    ->where(
                        'units.status',
                        UnitStatus::PROSES->value
                    ),
                'processing_units'
            )

            // Total repair pada unit yang pernah ditugaskan
            // ke teknisi ini.
            ->selectSub(
                UnitRepair::query()
                    ->selectRaw('COUNT(unit_repairs.id)')
                    ->whereExists(function ($query) {
                        $query
                            ->selectRaw('1')
                            ->from('unit_assignments')
                            ->whereColumn(
                                'unit_assignments.unit_id',
                                'unit_repairs.unit_id'
                            )
                            ->whereColumn(
                                'unit_assignments.technician_id',
                                'users.id'
                            );
                    }),
                'repair_count'
            );
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('total_units', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('total_units')
                    ->label('Jumlah Unit')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('completed_units')
                    ->label('Unit Selesai')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('processing_units')
                    ->label('Unit Proses')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('repair_count')
                    ->label('Repair')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\TechnicianPerformances\Pages\ListTechnicianPerformances::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }
}