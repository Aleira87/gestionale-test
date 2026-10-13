<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        || (
            $authUser->hasRole('company_admin')
            && $authUser->can('ViewAny:Company')
        );
    }

    public function view(User $authUser, Company $company): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        $isAuth = $authUser->hasRole('company_admin')
        && $authUser->managedCompanies()
            ->whereKey($company->getKey())
            ->exists()
        && $authUser->can('View:Company');

        return $isAuth;
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function update(User $authUser, Company $company): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->hasRole('company_admin')
        && $authUser->managedCompanies()
            ->whereKey($company->getKey())
            ->exists()
        && $authUser->can('Update:Company');
    }

    public function delete(User $authUser, Company $company): bool
    {
        // soltanto gli amministratori
        if (! $authUser->hasRole('super_admin')) {
            return false;
        }

        // ma non eliminiamo aziende coi dipendenti dentro
        return ! $company->users()->exists();
    }

    public function deleteAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('DeleteAny:Company');
    }

    public function restore(User $authUser, Company $company): bool
    {
        return $authUser->hasRole('super_admin')
            && $authUser->can('Restore:Company');
    }

    public function forceDelete(User $authUser, Company $company): bool
    {
        return $authUser->hasRole('super_admin')
            && $authUser->can('ForceDeleteAny:Company');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('ForceDeleteAny:Company');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('RestoreAny:Company');
    }

    public function replicate(User $authUser, Company $company): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('Replicate:Company');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('Reorder:Company');
    }
}
