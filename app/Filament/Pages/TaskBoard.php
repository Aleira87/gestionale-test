<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardPage;
use Relaticle\Flowforge\Column;
use Relaticle\Flowforge\Components\CardFlex;

class TaskBoard extends BoardPage
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Task Board';

    protected static ?string $title = 'Task Board';

    public function board(Board $board): Board
    {
        return $board
            ->query($this->getEloquentQuery())
            ->recordTitleAttribute('title')
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->cardSchema(fn (Schema $schema) => $schema->components([
                TextEntry::make('title')
                    ->weight('bold')
                    ->size('lg'),

                TextEntry::make('description')
                    ->limit(100)
                    ->color('gray')
                    ->placeholder('Nessuna descrizione'),

                CardFlex::make([
                    TextEntry::make('priority')
                        ->badge()
                        ->icon('heroicon-o-flag')
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'high' => 'Alta',
                            'medium' => 'Media',
                            'low' => 'Bassa',
                            default => $state,
                        })
                        ->color(fn ($state) => match ($state) {
                            'high' => 'danger',
                            'medium' => 'warning',
                            'low' => 'success',
                            default => 'gray',
                        }),

                    TextEntry::make('due_date')
                        ->date('d/m/Y')
                        ->icon('heroicon-o-calendar')
                        ->placeholder('Senza scadenza'),
                ])->wrap()->justify('start'),

                TextEntry::make('company.name')
                    ->icon('heroicon-o-building-office-2')
                    ->color('gray'),

                TextEntry::make('user.name')
                    ->icon('heroicon-o-user')
                    ->placeholder('Non assegnata')
                    ->color('gray'),
            ]))
            ->recordActions([
                $this->makeEditTaskAction(),
            ])
            ->columns([
                Column::make('todo')->label('Da fare')->color('gray'),
                Column::make('in_progress')->label('In Corso')->color('blue'),
                Column::make('done')->label('Completate')->color('green'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCreateTaskAction(),
        ];
    }

    protected function makeCreateTaskAction(): Action
    {
        return Action::make('createTask')
            ->label('Nuova attività')
            ->icon('heroicon-o-plus')
            ->visible(fn (): bool => $this->canManageTasks())
            ->modalHeading('Crea una nuova attività')
            ->schema($this->taskFormSchema())
            ->action(function (array $data): void {
                $this->validateTaskAssignment($data);

                $task = new Task;
                $task->fill($data);

                // La posizione viene assegnata esplicitamente:
                // non dipende da $fillable nel modello Task.
                $task->position = $this->nextPosition($data['status']);

                $task->save();
            });
    }

    protected function makeEditTaskAction(): Action
    {
        return Action::make('editTask')
            ->label('Modifica')
            ->icon('heroicon-o-pencil-square')
            ->visible(fn (): bool => $this->canManageTasks())
            ->modalHeading('Modifica attività')
            ->schema($this->taskFormSchema())
            ->fillForm(fn (array $record): array => [
                'title' => $record['title'],
                'description' => $record['description'] ?? null,
                'company_id' => $record['company_id'],
                'user_id' => $record['user_id'],
                'status' => $record['status'],
                'priority' => $record['priority'],
                'due_date' => $record['due_date'] ?? null,
            ])
            ->action(function (array $data, Task $record): void {
                // Non basta fidarsi dell'ID ricevuto dal form:
                // ricarichiamo il record attraverso lo scope della board.
                $task = $this->getEloquentQuery()
                    ->whereKey($record->getKey())
                    ->firstOrFail();

                $this->validateTaskAssignment($data);

                $task->fill($data);
                $task->save();
            });
    }

    protected function taskFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Titolo')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Descrizione')
                ->rows(3),

            Select::make('company_id')
                ->label('Azienda')
                ->options(fn (): array => $this->availableCompanies()
                    ->pluck('name', 'id')
                    ->all())
                ->required()
                ->searchable()
                ->live(),

            Select::make('user_id')
                ->label('Assegnatario')
                ->options(function (Get $get): array {
                    $companyId = $get('company_id');

                    if (! $companyId) {
                        return [];
                    }

                    return $this->availableAssignees((int) $companyId)
                        ->pluck('name', 'id')
                        ->all();
                })
                ->searchable()
                ->nullable(),

            Select::make('status')
                ->label('Stato')
                ->options([
                    'todo' => 'Da fare',
                    'in_progress' => 'In corso',
                    'done' => 'Completata',
                ])
                ->required()
                ->default('todo'),

            Select::make('priority')
                ->label('Priorità')
                ->options([
                    'low' => 'Bassa',
                    'medium' => 'Media',
                    'high' => 'Alta',
                ])
                ->required()
                ->default('medium'),

            DatePicker::make('due_date')
                ->label('Data di scadenza')
                ->native(false)
                ->nullable(),
        ];
    }

    protected function canManageTasks(): bool
    {
        $authUser = Auth::user();

        return $authUser !== null
            && (
                $authUser->hasRole('super_admin')
                || $authUser->hasRole('company_admin')
            );
    }

    protected function availableCompanies(): Builder
    {
        $authUser = Auth::user();

        if (! $authUser) {
            return Company::query()->whereRaw('1 = 0');
        }

        if ($authUser->hasRole('super_admin')) {
            return Company::query();
        }

        if ($authUser->hasRole('company_admin')) {
            return $authUser->managedCompanies()
                ->getQuery()
                ->getModel()
                ->newQuery()
                ->whereIn(
                    'id',
                    $authUser->managedCompanies()->select('companies.id')
                );
        }

        return Company::query()->whereRaw('1 = 0');
    }

    protected function availableAssignees(int $companyId): Builder
    {
        $authUser = Auth::user();

        if (! $authUser) {
            return User::query()->whereRaw('1 = 0');
        }

        if (
            ! $authUser->hasRole('super_admin')
            && ! $authUser->managedCompanies()
                ->where('companies.id', $companyId)
                ->exists()
        ) {
            return User::query()->whereRaw('1 = 0');
        }

        return User::query()
            ->where('company_id', $companyId)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'employee')
            );
    }

    protected function validateTaskAssignment(array $data): void
    {
        $companyId = (int) $data['company_id'];
        $userId = $data['user_id'] ?? null;

        if (! $this->availableCompanies()->whereKey($companyId)->exists()) {
            throw ValidationException::withMessages([
                'data.company_id' => 'Non puoi assegnare attività a questa azienda.',
            ]);
        }

        if (
            $userId !== null
            && ! $this->availableAssignees($companyId)
                ->whereKey((int) $userId)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'data.user_id' => 'L’assegnatario non appartiene all’azienda selezionata o non è autorizzato.',
            ]);
        }
    }

    protected function nextPosition(string $status): float|int
    {
        $query = $this->getEloquentQuery()
            ->where('status', $status);

        $maxPosition = $query->max('position');

        return $maxPosition === null
            ? 1
            : (float) $maxPosition + 1;
    }

    public function getEloquentQuery(): Builder
    {
        $authUser = Auth::user();
        $query = Task::query();

        if (! $authUser) {
            return $query->whereRaw('1 = 0');
        }

        if ($authUser->hasRole('super_admin')) {
            return $query;
        }

        if ($authUser->hasRole('company_admin')) {
            return $query->whereIn(
                'company_id',
                $authUser->managedCompanies()->select('companies.id')
            );
        }

        if ($authUser->hasRole('employee')) {
            return $query->where('user_id', $authUser->id);
        }

        return $query->whereRaw('1 = 0');
    }
}
