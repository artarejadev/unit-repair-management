<?php

namespace App\Filament\Resources\Trips;

use App\Filament\Resources\Trips\Pages\CreateTrip;
use App\Filament\Resources\Trips\Pages\EditTrip;
use App\Filament\Resources\Trips\Pages\ListTrips;
use App\Filament\Resources\Trips\Pages\ViewTrip;
use App\Filament\Resources\Trips\Schemas\TripForm;
use App\Filament\Resources\Trips\Schemas\TripInfolist;
use App\Filament\Resources\Trips\Tables\TripsTable;
use App\Enums\UnitStatus;
use App\Models\Trip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use App\Filament\Resources\Trips\RelationManagers\UnitsRelationManager;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $recordTitleAttribute = 'trip_number';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-map';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Trip';

    protected static ?string $modelLabel = 'Trip';

    protected static ?string $pluralModelLabel = 'Trip';

    public static function form(Schema $schema): Schema
    {
        return TripForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TripInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TripsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('customer')
            ->withCount('units')
            ->withCount([
                'units as pending_units_count' => fn (Builder $query) =>
                    $query->where('status', UnitStatus::PENDING->value),

                'units as proses_units_count' => fn (Builder $query) =>
                    $query->where('status', UnitStatus::PROSES->value),

                'units as selesai_units_count' => fn (Builder $query) =>
                    $query->where('status', UnitStatus::SELESAI->value),

                'units as ditagihkan_units_count' => fn (Builder $query) =>
                    $query->where('status', UnitStatus::DITAGIHKAN->value),

                'units as diambil_units_count' => fn (Builder $query) =>
                    $query->where('status', UnitStatus::DIAMBIL->value),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrips::route('/'),
            'create' => CreateTrip::route('/create'),
            'view' => ViewTrip::route('/{record}'),
            'edit' => EditTrip::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            UnitsRelationManager::class,
        ];
    }
}