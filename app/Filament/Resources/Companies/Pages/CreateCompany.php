<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;

class CreateCompany extends CreateRecord
{
    use HasWizard;

    protected static string $resource = CompanyResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Dati aziendali')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome Azienda')
                        ->placeholder('Es. Azienda S.r.l.')
                        ->required()
                        ->maxLength(255),
                ]),

            Step::make('Dati fiscali')
                ->schema([
                    TextInput::make('vat_number')
                        ->label('Partita IVA')
                        ->placeholder('0123456789')
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),
                ]),
        ];
    }
}
