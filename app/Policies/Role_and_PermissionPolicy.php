<?php

namespace App\Policies;

use App\Models\Role_and_Permission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class Role_and_PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->hasPermissionTo('read: permission'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role_and_Permission $roleAndPermission)
    {
        if ($user->hasPermissionTo('read: permission'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->hasPermissionTo('create: permission'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role_and_Permission $roleAndPermission)
    {
        if ($user->hasPermissionTo('update: permission'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role_and_Permission $roleAndPermission)
    {
        if ($user->hasPermissionTo('delete: permission'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role_and_Permission $roleAndPermission)
    {

    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role_and_Permission $roleAndPermission)
    {
    }
}
