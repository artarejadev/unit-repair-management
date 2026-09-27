<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Enums\UnitStatus;
use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillingStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $ditagihkan = Unit::query()
            ->where('status', UnitStatus::DITAGIHKAN->value)
            ->count();

        $issuedCount = Invoice::query()
            ->where('status', InvoiceStatus::ISSUED->value)
            ->count();

        $paidCount = Invoice::query()
            ->where('status', InvoiceStatus::PAID->value)
            ->count();

        $outstanding = (float) Invoice::query()
            ->where('status', InvoiceStatus::ISSUED->value)
            ->sum('total');

        return [
            Stat::make('Unit Ditagihkan', $ditagihkan)
                ->description('Menunggu pembayaran / pengambilan')
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('warning'),

            Stat::make('Invoice Belum Dibayar', $issuedCount)
                ->description('Status ISSUED')
                ->descriptionIcon('heroicon-o-clock')
                ->color('danger'),

            Stat::make('Invoice Lunas', $paidCount)
                ->description('Status PAID')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make(
                'Outstanding',
                'Rp ' . number_format($outstanding, 0, ',', '.')
            )
                ->description('Total invoice yang belum dibayar')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('danger'),
        ];
    }
}