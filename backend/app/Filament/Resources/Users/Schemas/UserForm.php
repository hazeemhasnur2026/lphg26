<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama')->required()->maxLength(255),

            TextInput::make('email')
                ->label('Surel')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Select::make('role')
                ->label('Peran')
                ->options(collect(UserRole::cases())->mapWithKeys(
                    fn (UserRole $peran) => [$peran->value => $peran->getLabel()]
                ))
                ->default(UserRole::Editor->value)
                ->required()
                ->helperText(collect(UserRole::cases())->map(
                    fn (UserRole $peran) => $peran->getLabel().': '.$peran->description()
                )->implode(' '))
                ->columnSpanFull(),

            TextInput::make('password')
                ->label('Kata sandi')
                ->password()
                ->revealable()
                ->minLength(12)
                // Wajib saat membuat, opsional saat menyunting: mengosongkannya
                // berarti tidak mengubah kata sandi yang sudah ada.
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->dehydrateStateUsing(fn (string $state) => Hash::make($state))
                ->helperText('Minimal 12 karakter. Kosongkan saat menyunting bila tidak ingin mengubahnya.')
                ->columnSpanFull(),
        ])->columns(2);
    }
}
