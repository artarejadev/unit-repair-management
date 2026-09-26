<?php

namespace App\Filament\Resources\Billings\Tables;

use App\Enums\UnitStatus;
use App\Filament\Resources\Billings\BillingResource;
use App\Models\Trip;
use App\Services\Billing\BillingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trip_number')
                    ->label('Trip')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trip_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('billing_units_count')
                    ->label('Siap Billing')
                    ->counts('billingUnits'),
            ])
            ->recordActions([
                Action::make('billing')
                    ->label('Buat Tagihan')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('success')
                    ->visible(
                        fn (Trip $record): bool =>
                            $record->billing_units_count > 0
                    )
                    ->requiresConfirmation()
                    ->action(function (Trip $record): void {
                        try {
                            $invoice = app(BillingService::class)
                                ->createForTrip($record);

                            Notification::make()
                                ->title('Billing berhasil dibuat')
                                ->body(
                                    $invoice->invoice_number
                                    . ' berhasil diterbitkan.'
                                )
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Billing gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}