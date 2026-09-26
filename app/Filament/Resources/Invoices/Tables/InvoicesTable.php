<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trip.trip_number')
                    ->label('No. Trip')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Tanggal Invoice')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Diterbitkan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])

            ->actions([
                ViewAction::make()
                    ->url(
                        fn (Invoice $record): string =>
                            InvoiceResource::getUrl(
                                'view',
                                ['record' => $record],
                            )
                    ),
            ])

            ->defaultSort('invoice_date', 'desc')

            ->recordUrl(
                fn (Invoice $record): string =>
                    InvoiceResource::getUrl(
                        'view',
                        ['record' => $record],
                    )
            );
    }
}