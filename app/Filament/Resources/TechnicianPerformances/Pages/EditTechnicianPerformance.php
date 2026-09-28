<?php

namespace App\Filament\Resources\TechnicianPerformances\Pages;

use App\Filament\Resources\TechnicianPerformances\TechnicianPerformanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTechnicianPerformance extends EditRecord
{
    protected static string $resource = TechnicianPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
