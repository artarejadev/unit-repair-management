<?php

namespace App\Filament\Resources\Trips\Pages;

use App\Filament\Resources\Trips\TripResource;
use App\Models\Trip;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTrip extends EditRecord
{
    protected static string $resource = TripResource::class;

    protected function beforeSave(): void
    {
        /** @var Trip $record */
        $record = $this->getRecord();

        $data = $this->form->getState();

        if (
            $record->units()->exists()
            && isset($data['customer_id'])
            && $data['customer_id'] !== $record->customer_id
        ) {
            Notification::make()
                ->danger()
                ->title('Customer tidak dapat diubah')
                ->body(
                    'Trip yang sudah memiliki Unit tidak boleh dipindahkan ke Customer lain.'
                )
                ->send();

            $this->halt();
        }
    }
}