<?php

namespace App\Filament\Resources\UnitRepairs\Pages;

use App\Filament\Resources\UnitRepairs\UnitRepairResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUnitRepair extends ViewRecord
{
    protected static string $resource = UnitRepairResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Repair'),
        ];
    }
}