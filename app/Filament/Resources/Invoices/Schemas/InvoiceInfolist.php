<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('trip.id')
                    ->label('Trip'),
                TextEntry::make('invoice_number')
                    ->placeholder('-'),
                TextEntry::make('invoice_date')
                    ->date(),
                TextEntry::make('status'),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('issued_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
