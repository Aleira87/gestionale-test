<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportBulkAction;
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportQuickHeaderAction;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                TableExportQuickHeaderAction::make(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nome Azienda')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vat_number')
                    ->label('Partita IVA')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data creazione')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Ultima modifica')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizza'),

                EditAction::make()
                    ->label('Modifica'),
            ])
            // disabilitatat cancellazione al momento per evitare incoerenza
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make()
                    // ->label('Elimina selezionate'),
                ]),
                TableExportBulkAction::make()
                    ->withColumns([
                        TextColumn::make('internal_notes')->label('Note'),
                    ]),
            ]);
    }
}
