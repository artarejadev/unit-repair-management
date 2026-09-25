<?php

namespace App\Filament\Resources\UnitRepairs;

use App\Filament\Resources\UnitRepairs\Pages\EditUnitRepair;
use App\Filament\Resources\UnitRepairs\Pages\ListUnitRepairs;
use App\Filament\Resources\UnitRepairs\Pages\ViewUnitRepair;
use App\Filament\Resources\UnitRepairs\RelationManagers\SparepartsRelationManager;
use App\Filament\Resources\UnitRepairs\Schemas\UnitRepairForm;
use App\Models\UnitRepair;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UnitRepairResource extends Resource
{
    protected static ?string $model = UnitRepair::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return UnitRepairForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getRelations(): array
    {
        return [
            SparepartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnitRepairs::route('/'),
            'view' => ViewUnitRepair::route('/{record}'),
            'edit' => EditUnitRepair::route('/{record}/edit'),
        ];
    }
}