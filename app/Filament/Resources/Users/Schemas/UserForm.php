<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserForm
{
    private static function availableCompanies(): array
    {
        $authUser = Auth::user();

        if (! $authUser) {
            return [];
        }

        // Il super admin può selezionare tutte le aziende.
        if ($authUser->hasRole('super_admin')) {
            return Company::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        // Il company admin può selezionare solo le aziende gestite.
        if ($authUser->hasRole('company_admin')) {
            return $authUser->managedCompanies()
                ->orderBy('companies.name')
                ->pluck('companies.name', 'companies.id')
                ->all();
        }

        // Gli altri ruoli non hanno aziende selezionabili.
        return [];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome')
                ->placeholder('Inserisci nome e cognome')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->maxLength(255),

            Select::make('role')
                ->label('Ruolo')
                ->options(
                    Role::query()
                        ->orderBy('name')
                        ->pluck('name', 'name')
                        ->all()
                )
                ->default('user')
                ->required()
                ->live()
                ->native(false),

            Select::make('company_id')
                ->label('Azienda di appartenenza')
                ->options(
                    fn (): array => self::availableCompanies()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()
                )
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => $get('role') === 'employee')
                ->visible(fn (Get $get): bool => $get('role') === 'employee'),

            Select::make('managed_company_ids')
                ->label('Aziende amministrate')
                ->options(
                    fn (): array => self::availableCompanies()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()
                )
                ->multiple()
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => $get('role') === 'company_admin')
                ->visible(fn (Get $get): bool => $get('role') === 'company_admin'),
        ]);
    }
}
