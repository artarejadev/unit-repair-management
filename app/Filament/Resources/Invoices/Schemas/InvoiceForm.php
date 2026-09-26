<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('trip_id')
                    ->relationship('trip', 'id')
                    ->required(),
                TextInput::make('invoice_number')
                    ->default(null),
                DatePicker::make('invoice_date')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('DRAFT'),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                DateTimePicker::make('issued_at'),
                DateTimePicker::make('paid_at'),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
