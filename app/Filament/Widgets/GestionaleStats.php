<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class GestionaleStats extends BaseWidget
{
    protected ?string $heading = 'Riepilogo gestionale';

    protected ?string $description = 'Una panoramica dei dati disponibili.';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'super_admin',
            'company_admin',
        ]) ?? false;
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        if ($user->hasRole('super_admin')) {
            $companiesCount = Company::query()->count();

            $employeesCount = User::query()
                ->whereHas('roles', function (Builder $query) {
                    $query->where('name', 'employee');
                })
                ->count();
        } else {
            $companiesCount = $user->managedCompanies()->count();

            $employeesCount = User::query()
                ->whereHas('roles', function (Builder $query) {
                    $query->where('name', 'employee');
                })
                ->whereIn(
                    'company_id',
                    $user->managedCompanies()->select('companies.id')
                )
                ->count();
        }

        return [
            Stat::make('Aziende', $companiesCount)
                ->description('Aziende disponibili')
                ->descriptionIcon('heroicon-m-building-office-2'),

            Stat::make('Dipendenti', $employeesCount)
                ->description('Dipendenti delle aziende accessibili')
                ->descriptionIcon('heroicon-m-users'),
        ];
    }
}
