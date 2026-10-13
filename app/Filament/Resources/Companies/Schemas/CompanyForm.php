<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome Azienda')
                    ->placeholder('Es. Azienda S.r.l.')
                    ->required()
                    ->maxLength(255),

                TextInput::make('vat_number')
                    ->label('Partita IVA')
                    ->placeholder('0123456789')
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
