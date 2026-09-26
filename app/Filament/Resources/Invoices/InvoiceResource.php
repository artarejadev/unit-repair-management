<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Invoice';

    protected static ?string $modelLabel = 'Invoice';

    protected static ?string $pluralModelLabel = 'Invoice';

    protected static string | \UnitEnum | null $navigationGroup = 'Keuangan';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check()
            && auth()->user()->isAdmin();
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Invoice')
                ->schema([
                    TextEntry::make('invoice_number')
                        ->label('No. Invoice'),

                    TextEntry::make('invoice_date')
                        ->label('Tanggal Invoice')
                        ->date('d/m/Y'),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge(),

                    TextEntry::make('issued_at')
                        ->label('Diterbitkan')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('paid_at')
                        ->label('Dibayar')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('-'),

                    TextEntry::make('total')
                        ->label('Total')
                        ->money('IDR'),
                ])
                ->columns(2),

            Section::make('Customer & Trip')
                ->schema([
                    TextEntry::make('customer.name')
                        ->label('Customer'),

                    TextEntry::make('trip.trip_number')
                        ->label('No. Trip'),
                ])
                ->columns(2),

            Section::make('Catatan')
                ->schema([
                    TextEntry::make('notes')
                        ->label('')
                        ->placeholder('-'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'view' => ViewInvoice::route('/{record}'),
        ];
    }
}