<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
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

            /*
            |--------------------------------------------------------------------------
            | Informasi Invoice
            |--------------------------------------------------------------------------
            */

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

                    TextEntry::make('canceled_at')
                        ->label('Dibatalkan')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('-'),

                    TextEntry::make('cancel_reason')
                        ->label('Alasan Pembatalan')
                        ->placeholder('-'),

                    TextEntry::make('canceledBy.name')
                        ->label('Dibatalkan Oleh')
                        ->placeholder('-'),

                    TextEntry::make('total')
                        ->label('Total')
                        ->money('IDR'),
                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | Customer & Trip
            |--------------------------------------------------------------------------
            */

            Section::make('Customer & Trip')
                ->schema([
                    TextEntry::make('customer.name')
                        ->label('Customer'),

                    TextEntry::make('trip.trip_number')
                        ->label('No. Trip'),
                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | Detail Tagihan
            |--------------------------------------------------------------------------
            */

            Section::make('Detail Tagihan')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->table([
                            TableColumn::make('Unit'),
                            TableColumn::make('Repair'),
                            TableColumn::make('Qty'),
                            TableColumn::make('Harga'),
                            TableColumn::make('Subtotal'),
                        ])
                        ->schema([
                            TextEntry::make('unit.imei')
                                ->label('Unit')
                                ->placeholder('-'),

                            TextEntry::make('description')
                                ->label('Repair')
                                ->placeholder('-'),

                            TextEntry::make('quantity')
                                ->label('Qty')
                                ->numeric(0),

                            TextEntry::make('unit_price')
                                ->label('Harga')
                                ->money('IDR'),

                            TextEntry::make('subtotal')
                                ->label('Subtotal')
                                ->money('IDR'),
                        ])
                        ->columns(5),
                ]),

            /*
            |--------------------------------------------------------------------------
            | Total
            |--------------------------------------------------------------------------
            */

            Section::make('Total Tagihan')
                ->schema([
                    TextEntry::make('total')
                        ->label('TOTAL')
                        ->money('IDR')
                        ->weight(\Filament\Support\Enums\FontWeight::Bold)
                        ->size(\Filament\Support\Enums\TextSize::Large),
                ])
                ->columns(1),

            /*
            |--------------------------------------------------------------------------
            | Catatan
            |--------------------------------------------------------------------------
            */

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