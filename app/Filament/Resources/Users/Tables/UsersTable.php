<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->state(function (User $record): string {
                        if ($record->hasRole('company_admin')) {
                            return $record->managedCompanies()
                                ->pluck('companies.name')
                                ->implode(', ') ?: 'Nessuna azienda';
                        }

                        return $record->company?->name ?? 'Nessuna azienda';
                    })
                    ->toggleable(),

                TextColumn::make('roles.name')
                    ->label('Ruolo')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Data creazione')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->label('Modifica'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Elimina selezionati'),
                ]),
            ]);
    }
}
