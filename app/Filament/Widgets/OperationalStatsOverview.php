<?php

namespace App\Filament\Widgets;

use App\Enums\UnitStatus;
use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationalStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total = Unit::query()->count();

        $pending = Unit::query()
            ->where('status', UnitStatus::PENDING->value)
            ->count();

        $proses = Unit::query()
            ->where('status', UnitStatus::PROSES->value)
            ->count();

        $selesai = Unit::query()
            ->where('status', UnitStatus::SELESAI->value)
            ->count();

        return [
            Stat::make('Total Unit', $total)
                ->description('Seluruh unit dalam sistem')
                ->descriptionIcon('heroicon-o-cube'),

            Stat::make('Pending', $pending)
                ->description('Belum mulai dikerjakan')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Proses', $proses)
                ->description('Sedang dikerjakan teknisi')
                ->descriptionIcon('heroicon-o-wrench-screwdriver')
                ->color('info'),

            Stat::make('Selesai', $selesai)
                ->description('Siap ditagihkan')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}