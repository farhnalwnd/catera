<?php

namespace App\Policies;

use App\Models\Unauthorized;
use App\Models\User;

class UnauthorizedPolicy
{
    /**
     * Determine whether the user can view any Unauthorized models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('catera:unauthorized:view_any');
    }

    /**
     * Determine whether the user can view the Unauthorized model.
     */
    public function view(User $user, Unauthorized $unauthorized): bool
    {
        return $user->hasPermissionTo('catera:unauthorized:view');
    }

    /**
     * Determine whether the user can create Unauthorized models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('catera:unauthorized:create');
    }

    /**
     * Determine whether the user can update the Unauthorized model.
     */
    public function update(User $user, Unauthorized $unauthorized): bool
    {
        return $user->hasPermissionTo('catera:unauthorized:update');
    }

    /**
     * Determine whether the user can delete the Unauthorized model.
     */
    public function delete(User $user, Unauthorized $unauthorized): bool
    {
        return $user->hasPermissionTo('catera:unauthorized:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Unauthorized $unauthorized): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Unauthorized $unauthorized): bool
    {
        return false;
    }
}
