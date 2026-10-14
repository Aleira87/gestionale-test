<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin')
            || (
                $user->hasRole('company_admin')
                && $user->can('ViewAny:User')
            );
    }

    public function view(User $user, User $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('company_admin')
            && $user->can('View:User')
            && $this->managesEmployee($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || (
                $user->hasRole('company_admin')
                && $user->can('Create:User')
            );
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('company_admin')
            && $user->can('Update:User')
            && $this->managesEmployee($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return $user->getKey() !== $model->getKey();
        }

        return $user->hasRole('company_admin')
            && $user->can('Delete:User')
            && $this->managesEmployee($user, $model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('super_admin')
            || (
                $user->hasRole('company_admin')
                && $user->can('DeleteAny:User')
            );
    }

    private function managesEmployee(
        User $user,
        User $model
    ): bool {
        if (
            ! $model->hasRole('employee')
            || $model->company_id === null
        ) {
            return false;
        }

        return $user->managedCompanies()
            ->whereKey($model->company_id)
            ->exists();
    }
}
