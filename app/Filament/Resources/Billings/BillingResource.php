<?php

namespace App\Filament\Resources\Billings;

use App\Filament\Resources\Billings\Pages\ListBillings;
use App\Models\Trip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use App\Filament\Resources\Billings\Tables\BillingsTable;

class BillingResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string | \UnitEnum | null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Billing Trip';

    protected static ?string $pluralModelLabel = 'Billing Trip';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return BillingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBillings::route('/'),
        ];
    }
}