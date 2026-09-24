<?php

namespace App\Filament\Resources\RepairTypes;

use App\Filament\Resources\RepairTypes\Pages\CreateRepairType;
use App\Filament\Resources\RepairTypes\Pages\EditRepairType;
use App\Filament\Resources\RepairTypes\Pages\ListRepairTypes;
use App\Filament\Resources\RepairTypes\Schemas\RepairTypeForm;
use App\Filament\Resources\RepairTypes\Tables\RepairTypesTable;
use App\Models\RepairType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RepairTypeResource extends Resource
{
    protected static ?string $model = RepairType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Jenis Repair';

    protected static ?string $pluralModelLabel = 'Jenis Repair';

    public static function form(Schema $schema): Schema
    {
        return RepairTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairTypes::route('/'),
            'create' => CreateRepairType::route('/create'),
            'edit' => EditRepairType::route('/{record}/edit'),
        ];
    }
}