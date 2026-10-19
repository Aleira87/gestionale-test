<?php

namespace App\Filament\Resources\Tickets;

use App\Models\Technician;
use App\Models\Ticket;
use daacreators\CreatorsTicketing\Filament\Resources\Tickets\TicketResource as BaseTicketResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TicketResource extends BaseTicketResource
{
    protected static ?string $model = Ticket::class;

    public static function form(Schema $schema): Schema
    {
        // Recuperiamo il form originale del plugin.
        $schema = parent::form($schema);

        // Recuperiamo i componenti già presenti.
        $components = $schema->getComponents();

        // Aggiungiamo una sezione dedicata ai nostri campi.
        $components[] = Section::make('Dati gestionali')
            ->schema([
                TextInput::make('primario')
                    ->label('Primario')
                    ->maxLength(255),

                TextInput::make('secondario')
                    ->label('Secondario')
                    ->maxLength(255)
                    ->rules([
                        fn (Get $get, ?Model $record) => Rule::unique('ct_tickets', 'secondario')
                            ->where('primario', $get('primario'))
                            ->ignore($record?->getKey()),
                    ]),
                Select::make('supplier_id')
                    ->label('Fornitore')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('technician_id', null);
                    }),
                Select::make('technician_id')
                    ->label('Tecnico')
                    ->options(function (Get $get): array {
                        $supplierId = $get('supplier_id');

                        if (! $supplierId) {
                            return [];
                        }

                        return Technician::query()
                            ->where('supplier_id', $supplierId)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->disabled(fn (Get $get): bool => ! $get('supplier_id')),
                DateTimePicker::make('opened_at')
                    ->label('Data apertura')
                    ->seconds(false),

                DateTimePicker::make('on_site_at')
                    ->label('Data On Site')
                    ->seconds(false)
                    ->afterOrEqual('opened_at'),

                DateTimePicker::make('closed_at')
                    ->label('Data chiusura')
                    ->seconds(false)
                    ->rules([
                        'after_or_equal:opened_at',
                        'after_or_equal:on_site_at',
                    ])
            ])
            ->columns(2);

        // Restituiamo lo schema completo.
        return $schema->components($components);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }
}
