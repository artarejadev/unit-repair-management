<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use Filament\Resources\Pages\ViewRecord;

use App\Enums\UnitStatus;
use App\Services\Units\TechnicianUnitWorkflowService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (auth()->user()?->isTechnician()) {
            $actions[] = Action::make('start')
                ->label('Mulai Kerjakan')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(
                    fn (): bool =>
                        $this->record->status === UnitStatus::PENDING
                        && $this->record->isAssignedTo(auth()->user())
                )
                ->requiresConfirmation()
                ->action(function (): void {
                    app(TechnicianUnitWorkflowService::class)->start(
                        unit: $this->record,
                        technician: auth()->user(),
                    );

                    Notification::make()
                        ->title('Unit masuk PROSES')
                        ->success()
                        ->send();

                    $this->refreshFormData([
                        'status',
                    ]);
                });

            $actions[] = Action::make('finish')
                ->label('Tandai Selesai')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(
                    fn (): bool =>
                        $this->record->status === UnitStatus::PROSES
                        && $this->record->isAssignedTo(auth()->user())
                )
                ->requiresConfirmation()
                ->action(function (): void {
                    app(TechnicianUnitWorkflowService::class)->finish(
                        unit: $this->record,
                        technician: auth()->user(),
                    );

                    Notification::make()
                        ->title('Unit ditandai SELESAI')
                        ->success()
                        ->send();

                    $this->refreshFormData([
                        'status',
                    ]);
                });
        }

        return $actions;
    }
}