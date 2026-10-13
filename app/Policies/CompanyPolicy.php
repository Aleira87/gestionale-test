<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        || (
            $authUser->hasRole('company_admin')
            && $authUser->can('ViewAny:Company')
        );
    }

    public function view(AuthUser $authUser, Company $company): bool
    {
        if ($authUser->hasRole('super_admin'))
            {
                return true;
            }

            return $authUser->hasRole('company_admin')
            && $authUser->company_id === $company->id
            && $authUser->can('View:Company');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Company $company): bool
    {
            if ($authUser->hasRole('super_admin'))
                {
                    return true;
                }

                return false;
                //momentaneamente false... da aggiornare una volta definito che IL COMPANY ADMIN abbia più aziende
                // return $authUser->hasRole('company_admin')
                // && $authUser->company_id === $company->id
                // && $authUser->can('Update:Company');
    }

    public function delete(AuthUser $authUser, Company $company): bool
    {
        // soltanto gli amministratori
        if (! $authUser->hasRole('super_admin'))
            {
                return false;
            }
        // ma non eliminiamo aziende coi dipendenti dentro
        return ! $company->users()->exists();
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('DeleteAny:Company');
    }

    public function restore(AuthUser $authUser, Company $company): bool
    {
        return $authUser->hasRole('super_admin')
            && $authUser->can('Restore:Company');
    }

    public function forceDelete(AuthUser $authUser, Company $company): bool
    {
        return $authUser->hasRole('super_admin')
            && $authUser->can('ForceDeleteAny:Company');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('ForceDeleteAny:Company');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('RestoreAny:Company');
    }

    public function replicate(AuthUser $authUser, Company $company): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('Replicate:Company');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin')
        && $authUser->can('Reorder:Company');
    }

}
