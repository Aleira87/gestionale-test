<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Company;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportQuickHeaderAction;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->headerActions([
            TableExportQuickHeaderAction::make(),
        ])
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
            ->filters([
                SelectFilter::make('role')
                    ->label('Ruolo')
                    ->options([
                        'super_admin' => 'Super amministratore',
                        'company_admin' => 'Amministratore azienda',
                        'employee' => 'Dipendente',
                        'user' => 'Utente',
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn ($query, $role) => $query->whereHas(
                                'roles',
                                fn ($roleQuery) => $roleQuery->where('name', $role)
                            )
                        );
                    }),
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->options(
                        Company::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn ($query, $companyId) => $query->where('company_id', $companyId)
                        );
                    }),
            ])
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
