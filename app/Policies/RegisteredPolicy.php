<?php

namespace App\Policies;

use App\Models\Registered;
use App\Models\User;

class RegisteredPolicy
{
    /**
     * Determine whether the user can view any Registered models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('catera:quota_scheduling:view_any');
    }

    /**
     * Determine whether the user can view the Registered model.
     */
    public function view(User $user, Registered $registered): bool
    {
        return $user->hasPermissionTo('catera:quota_scheduling:view');
    }

    /**
     * Determine whether the user can create Registered models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('catera:quota_scheduling:create');
    }

    /**
     * Determine whether the user can update the Registered model.
     */
    public function update(User $user, Registered $registered): bool
    {
        return $user->hasPermissionTo('catera:quota_scheduling:update');
    }

    /**
     * Determine whether the user can delete the Registered model.
     */
    public function delete(User $user, Registered $registered): bool
    {
        return $user->hasPermissionTo('catera:quota_scheduling:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Registered $registered): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Registered $registered): bool
    {
        return false;
    }
}
