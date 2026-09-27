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
                Section::make('Status Pembayaran & Pengambilan')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status Invoice')
                            ->badge(),

                        TextEntry::make('paid_at')
                            ->label('Dibayar Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum dibayar'),

                        TextEntry::make('picked_up_at')
                            ->label('Diambil Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum diambil'),

                        TextEntry::make('pickedUpBy.name')
                            ->label('Diambil Oleh')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
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
