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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                    ->afterOrEqual('opened_at')
                    ->afterOrEqual('on_site_at'),
            ])
            ->columns(2)
            ->columnSpanFull();

        // Restituiamo lo schema completo.
        return $schema->components($components);
    }

    public static function table(Table $table): Table
    {
        $table = parent::table($table);

        $columns = $table->getColumns();

        $columns[] = TextColumn::make('supplier.name')
            ->label('Fornitore')
            ->placeholder('Non assegnato')
            ->searchable()
            ->sortable()
            ->toggleable();

        $columns[] = TextColumn::make('technician.name')
            ->label('Tecnico')
            ->placeholder('Non assegnato')
            ->searchable()
            ->sortable()
            ->toggleable();

        $columns[] = TextColumn::make('primario')
            ->label('Primario')
            ->placeholder('Non specificato')
            ->searchable()
            ->sortable()
            ->toggleable();

        $columns[] = TextColumn::make('secondario')
            ->label('Secondario')
            ->placeholder('Non specificato')
            ->searchable()
            ->sortable()
            ->toggleable();

        $columns[] = TextColumn::make('on_site_at')
            ->label('Data On Site')
            ->dateTime('d/m/Y H:i')
            ->placeholder('Non specificata')
            ->sortable()
            ->toggleable();

        $filters = $table->getFilters();

        $filters[] = SelectFilter::make('supplier_id')
            ->label('Fornitore')
            ->relationship('supplier', 'name')
            ->searchable()
            ->preload();

        $filters[] = SelectFilter::make('technician_id')
            ->label('Tecnico')
            ->relationship(
                'technician',
                'name',
                modifyQueryUsing: function (Builder $query, $livewire): void {
                    $supplierId = data_get(
                        $livewire->getTableFilterFormState('supplier_id'),
                        'value'
                    );

                    if (filled($supplierId)) {
                        $query->where('supplier_id', $supplierId);
                    }
                }
            )
            ->searchable()
            ->preload();

        return $table
            ->columns($columns)
            ->filters($filters);
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
