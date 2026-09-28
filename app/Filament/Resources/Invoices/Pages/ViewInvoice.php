<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceCancellationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

use App\Enums\InvoiceStatus;
use App\Services\InvoicePaymentService;
use App\Services\InvoicePickupService;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Invoice')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(
                    fn (): string => route(
                        'invoices.print',
                        [
                            'invoice' => $this->record,
                        ],
                    )
                )
                ->openUrlInNewTab(),

            Action::make('cancel')
                ->label('Batalkan Invoice')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Batalkan Invoice')
                ->modalDescription(
                    'Invoice akan menjadi CANCELED dan seluruh unit dalam invoice akan dikembalikan ke status SELESAI.'
                )
                ->schema([
                    Textarea::make('reason')
                        ->label('Alasan Pembatalan')
                        ->required()
                        ->minLength(5)
                        ->maxLength(1000)
                        ->rows(4),
                ])
                ->modalSubmitActionLabel('Batalkan Invoice')
                ->visible(
                    fn (Invoice $record): bool =>
                        $record->status === InvoiceStatus::ISSUED
                )
                ->action(function (
                    Invoice $record,
                    array $data,
                ): void {
                    try {
                        app(InvoiceCancellationService::class)->cancel(
                            $record,
                            auth()->user(),
                            $data['reason'],
                        );

                        Notification::make()
                            ->success()
                            ->title('Invoice berhasil dibatalkan')
                            ->body(
                                "Invoice {$record->invoice_number} sekarang berstatus CANCELED."
                            )
                            ->send();

                        $this->refreshFormData([
                            'status',
                            'canceled_by',
                            'canceled_at',
                            'cancel_reason',
                        ]);
                    } catch (Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal membatalkan invoice')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('markAsPaid')
                ->label('Tandai Sudah Dibayar')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn (Invoice $record) =>
                    auth()->user()?->isAdmin()
                    && $record->status === InvoiceStatus::ISSUED
                )
                ->requiresConfirmation()
                ->modalHeading('Tandai Invoice Sudah Dibayar')
                ->modalDescription(
                    'Invoice akan berubah menjadi PAID. Setelah itu pembayaran tidak dapat dibatalkan melalui sistem.'
                )
                ->action(function (Invoice $record): void {
                    app(InvoicePaymentService::class)->markAsPaid(
                        $record,
                        auth()->user()
                    );
                })
                ->successNotificationTitle('Invoice berhasil ditandai sudah dibayar'),

            Action::make('markAsPickedUp')
                ->label('Tandai Sudah Diambil')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->visible(fn (Invoice $record) =>
                    auth()->user()?->isAdmin()
                    && $record->status === InvoiceStatus::PAID
                    && $record->picked_up_at === null
                )
                ->requiresConfirmation()
                ->modalHeading('Tandai Unit Sudah Diambil')
                ->modalDescription(
                    'Semua unit yang terdapat pada invoice ini akan ditandai sebagai DIAMBIL.'
                )
                ->action(function (Invoice $record): void {
                    app(InvoicePickupService::class)->markAsPickedUp(
                        $record,
                        auth()->user()
                    );
                })
                ->successNotificationTitle('Invoice dan unit berhasil ditandai sudah diambil'),
        ];
    }
}