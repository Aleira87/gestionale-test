<?php

namespace App\Filament\Pages;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardPage;
use Relaticle\Flowforge\Column;

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
            ->positionIdentifier('position') // Enable drag-and-drop with position field
            ->columns([
                Column::make('todo')->label('Da fare')->color('gray'),
                Column::make('in_progress')->label('In Corso')->color('blue'),
                Column::make('done')->label('Completate')->color('green'),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        $authUser = Auth::user();
        $query = Task::query();

        if (! $authUser) {
            return $query->whereRaw('1 = 0');
        }

        // Il super admin può vedere tutte le attività.
        if ($authUser->hasRole('super_admin')) {
            return $query;
        }

        // Il responsabile vede le attività delle aziende che gestisce.
        if ($authUser->hasRole('company_admin')) {
            return $query->whereIn(
                'company_id',
                $authUser->managedCompanies()->select('companies.id')
            );
        }

        // Il dipendente vede solo le attività assegnate a lui.
        if ($authUser->hasRole('employee')) {
            return $query->where('user_id', $authUser->id);
        }

        // Gli altri ruoli non vedono attività.
        return $query->whereRaw('1 = 0');
    }
}
