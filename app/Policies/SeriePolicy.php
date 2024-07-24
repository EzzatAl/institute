<?php

namespace App\Policies;

use App\Models\Serie;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SeriePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if($user->hasPermissionTo('serie permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Serie $permission)
    {
        if($user->hasPermissionTo('serie permissions'))
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
        if($user->hasPermissionTo('serie permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Serie $permission)
    {
        if($user->hasPermissionTo('serie permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Serie $permission)
    {
        if($user->hasPermissionTo('serie permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Serie $permission)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Serie $permission)
    {
        //
    }
}
