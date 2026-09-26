<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

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
        ];
    }
}