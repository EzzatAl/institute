<?php

namespace App\Policies;

use App\Models\Employee_Schedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class E_SchedulesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if($user->hasPermissionTo('T-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employee_Schedule $permission)
    {
        if($user->hasPermissionTo('T-schedules permissions'))
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
        if($user->hasPermissionTo('T-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee_Schedule $permission)
    {
        if($user->hasPermissionTo('T-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee_Schedule $permission)
    {
        if($user->hasPermissionTo('T-schedules permissions'))
        {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employee_Schedule $permission)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employee_Schedule $permission)
    {
        //
    }
}
