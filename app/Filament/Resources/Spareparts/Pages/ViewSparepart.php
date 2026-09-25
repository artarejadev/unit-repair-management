<?php

namespace App\Filament\Resources\Spareparts\Pages;

use App\Filament\Resources\Spareparts\SparepartResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSparepart extends ViewRecord
{
    protected static string $resource = SparepartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
