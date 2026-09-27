<?php

namespace App\Filament\Widgets;

use App\Enums\UnitStatus;
use App\Models\Unit;
use Filament\Widgets\ChartWidget;

class UnitStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Distribusi Status Unit';
    }

    protected function getData(): array
    {
        $statuses = [
            UnitStatus::PENDING,
            UnitStatus::PROSES,
            UnitStatus::SELESAI,
            UnitStatus::DITAGIHKAN,
            UnitStatus::DIAMBIL,
        ];

        $labels = [];
        $data = [];

        foreach ($statuses as $status) {
            $labels[] = match ($status) {
                UnitStatus::PENDING => 'Pending',
                UnitStatus::PROSES => 'Proses',
                UnitStatus::SELESAI => 'Selesai',
                UnitStatus::DITAGIHKAN => 'Ditagihkan',
                UnitStatus::DIAMBIL => 'Diambil',
                default => $status->value,
            };

            $data[] = Unit::query()
                ->where('status', $status->value)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Unit',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}