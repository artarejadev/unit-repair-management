<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof UserRole
                                ? $state->label()
                                : (string) $state
                    )
                    ->color(
                        fn ($state): string =>
                            $state === UserRole::ADMIN
                                || $state === UserRole::ADMIN->value
                                ? 'primary'
                                : 'info'
                    )
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (bool $state): string =>
                            $state ? 'Aktif' : 'Nonaktif'
                    )
                    ->color(
                        fn (bool $state): string =>
                            $state ? 'success' : 'danger'
                    )
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        UserRole::ADMIN->value => UserRole::ADMIN->label(),
                        UserRole::TEKNISI->value => UserRole::TEKNISI->label(),
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ]),
            ])

            ->recordActions([
                EditAction::make(),

                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->schema([
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->minLength(8)
                            ->confirmed()
                            ->required(),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(),
                    ])
                    ->modalHeading('Reset Password')
                    ->modalDescription(
                        fn (User $record): string =>
                            "Masukkan password baru untuk {$record->name}."
                    )
                    ->modalSubmitActionLabel('Reset Password')
                    ->action(
                        function (User $record, array $data): void {
                            $record->update([
                                'password' => $data['password'],
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Password berhasil diubah')
                                ->body(
                                    "Password {$record->name} telah diperbarui."
                                )
                                ->send();
                        }
                    ),

                DeleteAction::make(),
            ])

            ->defaultSort('name');
    }
}