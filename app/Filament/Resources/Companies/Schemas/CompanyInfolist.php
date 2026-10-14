<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati aziendali')
                    ->description('Informazioni identificative dell’azienda.')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nome azienda')
                                    ->icon(Heroicon::OutlinedBuildingOffice),

                                TextEntry::make('vat_number')
                                    ->label('Partita IVA')
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->placeholder('Non specificata'),
                            ]),
                    ]),

                Section::make('Informazioni di sistema')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Data di creazione')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('—'),

                                TextEntry::make('updated_at')
                                    ->label('Ultima modifica')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('—'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
