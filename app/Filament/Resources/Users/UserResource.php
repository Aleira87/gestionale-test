<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $authUser = Auth::user();

        if (! $authUser) {
            return $query->whereRaw('1 = 0');
        }

        // Il super admin può recuperare tutti gli utenti.
        if ($authUser->hasRole('super_admin')) {
            return $query;
        }

        // Il company admin vede solo i dipendenti delle aziende gestite.
        if ($authUser->hasRole('company_admin')) {
            return $query
                ->whereHas('roles', function (Builder $roleQuery): void {
                    $roleQuery->where('name', 'employee');
                })
                ->whereIn(
                    'company_id',
                    $authUser->managedCompanies()->select('companies.id')
                );
        }

        // Gli altri ruoli non ottengono l'elenco degli utenti.
        return $query->whereRaw('1 = 0');
    }
}
