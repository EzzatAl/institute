<?php

namespace App\Policies;

use App\Models\Classroom_Schedules;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class C_SchedulesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if($user->hasPermissionTo('C-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Classroom_Schedules $permission)
    {
        if($user->hasPermissionTo('C-schedules permissions'))
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
        if($user->hasPermissionTo('C-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Classroom_Schedules $permission)
    {
        if($user->hasPermissionTo('C-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Classroom_Schedules $permission)
    {
        if($user->hasPermissionTo('C-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Classroom_Schedules $permission)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Classroom_Schedules $permission)
    {
        //
    }
}
