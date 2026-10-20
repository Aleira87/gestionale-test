<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Supplier;
use App\Models\Technician;
use daacreators\CreatorsTicketing\Filament\Resources\Tickets\Pages\ViewTicket as BaseViewTicket;
use daacreators\CreatorsTicketing\Models\TicketStatus;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ViewTicket extends BaseViewTicket
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),

            Action::make('editManagementData')
                ->label('Modifica dati gestionali')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn (): bool => Filament::auth()->user()?->hasRole('super_admin') ?? false
                )
                ->fillForm(fn (): array => [
                    'primario' => $this->record->primario,
                    'secondario' => $this->record->secondario,
                    'supplier_id' => $this->record->supplier_id,
                    'technician_id' => $this->record->technician_id,
                    'opened_at' => $this->record->opened_at,
                    'on_site_at' => $this->record->on_site_at,
                    'closed_at' => $this->record->closed_at,
                ])
                ->schema([
                    TextInput::make('primario')
                        ->label('Primario')
                        ->maxLength(255),

                    TextInput::make('secondario')
                        ->label('Secondario')
                        ->maxLength(255)
                        ->rules([
                            fn (Get $get) => Rule::unique('ct_tickets', 'secondario')
                                ->where('primario', $get('primario'))
                                ->ignore($this->record->getKey()),
                        ])
                        ->validationMessages([
                            'unique' => 'La combinazione di Primario e Secondario è già presente in un altro ticket.',
                        ]),

                    Select::make('supplier_id')
                        ->label('Fornitore')
                        ->options(fn (): array => Supplier::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()
                        )
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(
                            fn (Set $set) => $set('technician_id', null)
                        ),

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
                        ->disabled(fn (Get $get): bool => ! $get('supplier_id')
                        ),

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
                ->modalHeading('Modifica dati gestionali')
                ->modalSubmitActionLabel('Salva modifiche')
                ->action(function (array $data): void {
                    abort_unless(
                        Filament::auth()->user()?->hasRole('super_admin'),
                        403
                    );

                    $this->record->update($data);

                    $this->record->refresh();

                    Notification::make()
                        ->title('Dati gestionali aggiornati')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        $schema = parent::infolist($schema);

        $components = $schema->getComponents();

        foreach ($components as $component) {
            if ($component instanceof Tabs) {
                $tabs = $component->getChildComponents();

                $tabs[] = Tab::make('Dati gestionali')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Section::make('Informazioni gestionali')
                            ->schema([
                                TextEntry::make('primario')
                                    ->label('Primario')
                                    ->placeholder('Non specificato'),

                                TextEntry::make('secondario')
                                    ->label('Secondario')
                                    ->placeholder('Non specificato'),

                                TextEntry::make('supplier.name')
                                    ->label('Fornitore')
                                    ->placeholder('Non assegnato'),

                                TextEntry::make('technician.name')
                                    ->label('Tecnico')
                                    ->placeholder('Non assegnato'),

                                TextEntry::make('opened_at')
                                    ->label('Data apertura')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Non specificata'),

                                TextEntry::make('on_site_at')
                                    ->label('Data On Site')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Non specificata'),

                                TextEntry::make('closed_at')
                                    ->label('Data chiusura')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Non specificata'),
                            ])
                            ->columns(2),
                    ]);

                $component->tabs($tabs);

                break;
            }
        }

        return $schema->components($components);
    }

    public function changeStatus($statusId): void
    {
        $status = TicketStatus::find($statusId);

        if ($status?->is_closing_status && ! $this->record->closed_at) {
            Notification::make()
                ->title('Impossibile chiudere il ticket')
                ->body('Inserisci prima la Data chiusura tramite Modifica dati gestionali.')
                ->danger()
                ->send();

            return;
        }

        try {
            parent::changeStatus($statusId);

        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Cambio stato non consentito')
                ->body(collect($exception->errors())->flatten()->first()
                    ?? 'Verifica i dati del ticket.')
                ->danger()
                ->send();
        }
    }
}
