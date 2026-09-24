<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255)
                    ->autocapitalize('words'),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: User::class,
                        column: 'email',
                        ignoreRecord: true,
                    ),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        UserRole::ADMIN->value => UserRole::ADMIN->label(),
                        UserRole::TEKNISI->value => UserRole::TEKNISI->label(),
                    ])
                    ->required()
                    ->searchable()
                    ->disabled(
                        fn (?User $record): bool =>
                            $record?->is(auth()->user()) ?? false
                    ),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->disabled(
                        fn (?User $record): bool =>
                            $record?->is(auth()->user()) ?? false
                    ),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->minLength(8)
                    ->confirmed()
                    ->required(
                        fn (string $operation): bool =>
                            $operation === Operation::Create->value
                    )
                    ->hiddenOn(Operation::Edit),

                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->required(
                        fn (string $operation): bool =>
                            $operation === Operation::Create->value
                    )
                    ->hiddenOn(Operation::Edit),
            ]);
    }
}