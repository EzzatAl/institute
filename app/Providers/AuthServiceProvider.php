<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Audio;
use App\Models\Classroom;
use App\Models\Classroom_Schedules;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use App\Models\Exam;
use App\Models\Media;
use App\Models\Permission;
use App\Models\PlacementTest;
use App\Models\Role;
use App\Models\Role_and_Permission;
use App\Models\Serie;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Policies\AudioPolicy;
use App\Policies\C_SchedulesPolicy;
use App\Policies\ClassroomPolicy;
use App\Policies\CoursePolicy;
use App\Policies\E_SchedulesPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ExamPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\Role_and_PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\PlacementTestPolicy;
use App\Policies\SeriePolicy;
use App\Policies\StudentPolicy;
use App\Policies\SubjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class =>UserPolicy::class,
        Role::class =>RolePolicy::class,
        Role_and_Permission::class =>Role_and_PermissionPolicy::class,
        Permission::class =>PermissionPolicy::class,
        Audio::class =>AudioPolicy::class,
        Classroom_Schedules::class =>C_SchedulesPolicy::class,
        Classroom::class =>ClassroomPolicy::class,
        Course::class =>CoursePolicy::class,
        Employee_Schedule::class =>E_SchedulesPolicy::class,
        Employee::class =>EmployeePolicy::class,
        Exam::class =>ExamPolicy::class,
        Media::class =>MediaPolicy::class,
        PlacementTest::class =>PlacementTestPolicy::class,
        Serie::class =>SeriePolicy::class,
        Student::class =>StudentPolicy::class,
        Subject::class =>SubjectPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
