<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use Filament\Resources\Pages\ViewRecord;

use App\Enums\UnitStatus;
use App\Services\Units\TechnicianUnitWorkflowService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Unit;
use App\Services\UnitPickupService;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        $actions[] = Action::make('markRework')
            ->label('Tandai Rework')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Tandai Unit sebagai Rework')
            ->modalDescription(
                'Unit akan dikembalikan ke alur pengerjaan teknisi.'
            )
            ->visible(
                fn (Unit $record): bool =>
                    auth()->user()?->isAdmin()
                    && $record->status === UnitStatus::SELESAI
            )
            ->action(function (Unit $record): void {
                $record->update([
                    'status' => UnitStatus::REWORK,
                ]);

                Notification::make()
                    ->success()
                    ->title('Unit ditandai REWORK')
                    ->body(
                        "Unit {$record->imei} siap dikerjakan kembali."
                    )
                    ->send();
            });

        $actions[] = Action::make('markAsPickedUp')
            ->label('Tandai Sudah Diambil')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Unit $record) =>
                auth()->user()?->isAdmin()
                && $record->status === UnitStatus::DITAGIHKAN
            )
            ->requiresConfirmation()
            ->modalHeading('Tandai Unit Sudah Diambil')
            ->modalDescription(
                'Pastikan unit benar-benar sudah diserahkan kepada customer sebelum melanjutkan.'
            )
            ->action(function (Unit $record): void {
                app(UnitPickupService::class)->markAsPickedUp(
                    $record,
                    auth()->user()
                );
            })
            ->successNotificationTitle('Unit berhasil ditandai sudah diambil');

        if (auth()->user()?->isTechnician()) {
            $actions[] = Action::make('start')
                ->label('Mulai Kerjakan')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(
                    fn (): bool =>
                        in_array(
                            $this->record->status,
                            [
                                UnitStatus::PENDING,
                                UnitStatus::REWORK,
                            ],
                            true
                        )
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