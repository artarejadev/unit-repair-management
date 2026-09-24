<?php

namespace App\Filament\Resources\RepairTypes\Pages;

use App\Filament\Resources\RepairTypes\RepairTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairTypes extends ListRecords
{
    protected static string $resource = RepairTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Jenis Repair'),
        ];
    }
}