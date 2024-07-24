<?php

namespace App\Policies;

use App\Models\PlacementTest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlacementTestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if($user->hasPermissionTo('placement-test permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user,PlacementTest $permission)
    {
        if($user->hasPermissionTo('placement-test permissions'))
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
        if($user->hasPermissionTo('placement-test permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user,PlacementTest $permission)
    {
        if($user->hasPermissionTo('placement-test permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user,PlacementTest $permission)
    {
        if($user->hasPermissionTo('placement-test permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user,PlacementTest $permission)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user,PlacementTest $permission)
    {
        //
    }
}
