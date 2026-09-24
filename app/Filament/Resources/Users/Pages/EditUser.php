<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        /** @var User $record */
        $record = $this->getRecord();

        $data = $this->form->getState();

        /*
         * User tidak boleh menonaktifkan dirinya sendiri
         * atau mengubah role dirinya sendiri.
         */
        if ($record->is(auth()->user())) {
            if (
                ($data['role'] ?? $record->role->value)
                    !== $record->role->value
                || ! ($data['is_active'] ?? true)
            ) {
                Notification::make()
                    ->danger()
                    ->title('Perubahan tidak diizinkan')
                    ->body(
                        'Anda tidak dapat mengubah role atau menonaktifkan akun sendiri.'
                    )
                    ->send();

                $this->halt();

                return;
            }
        }

        /*
         * Pastikan selalu ada minimal satu active ADMIN.
         */
        $newRole = $data['role']
            ?? $record->role->value;

        $newIsActive = (bool) (
            $data['is_active']
            ?? $record->is_active
        );

        $changingLastAdmin = (
            $record->isAdmin()
            && $record->is_active
            && (
                $newRole !== UserRole::ADMIN->value
                || ! $newIsActive
            )
        );

        if ($changingLastAdmin) {
            $otherActiveAdminExists = User::query()
                ->where('role', UserRole::ADMIN->value)
                ->where('is_active', true)
                ->whereKeyNot($record->getKey())
                ->exists();

            if (! $otherActiveAdminExists) {
                Notification::make()
                    ->danger()
                    ->title('Minimal satu Admin aktif diperlukan')
                    ->body(
                        'Role atau status Admin terakhir tidak dapat diubah.'
                    )
                    ->send();

                $this->halt();
            }
        }
    }
}