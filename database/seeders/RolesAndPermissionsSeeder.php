<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                // misc
                $miscPermission = Permission::create(['name'=>'N/A']);

                // user model
                $userPermission1 = Permission::create(['name'=>'create: user']);
                $userPermission2 = Permission::create(['name'=>'read: user']);
                $userPermission3 = Permission::create(['name'=>'update: user']);
                $userPermission4 = Permission::create(['name'=>'delete: user']);

                // role model
                $rolePermission1 = Permission::create(['name'=>'create: role']);
                $rolePermission2 = Permission::create(['name'=>'read: role']);
                $rolePermission3 = Permission::create(['name'=>'update: role']);
                $rolePermission4 = Permission::create(['name'=>'delete: role']);

                // permission model
                $permission1 = Permission::create(['name'=>'create: permission']);
                $permission2 = Permission::create(['name'=>'read: permission']);
                $permission3 = Permission::create(['name'=>'update: permission']);
                $permission4 = Permission::create(['name'=>'delete: permission']);

                // admins
                $adminPermission1 = Permission::create(['name'=>'read: admin']);
                $adminPermission2 = Permission::create(['name'=>'update: admin']);
                // course model
                $coursePermission1 = Permission::create(['name'=>'course permissions']);
                // exam model
                $examPermission1 = Permission::create(['name'=>'exam permissions']);
                // media model
                $mediaPermission1 = Permission::create(['name'=>'media permissions']);
                // student model
                $studentPermission1 = Permission::create(['name'=>'student permissions']);
                // classroom model
                $classroomPermission1 = Permission::create(['name'=>'classroom permissions']);
                // C_schedules model
                $C_schedulesPermission1 = Permission::create(['name'=>'C-schedules permissions']);
                // teacher model
                $teacherPermission1 = Permission::create(['name'=>'Teacher permissions']);
                // T_schedules model
                $T_schedulesPermission1 = Permission::create(['name'=>'T-schedules permissions']);
                // placement_test model
                $placement_testPermission1 = Permission::create(['name'=>'placement-test permissions']);
                // audio model
                $audioPermission1 = Permission::create(['name'=>'audio permissions']);
                // serie model
                $seriePermission1 = Permission::create(['name'=>'serie permissions']);
                // subject model
                $subjectPermission1 = Permission::create(['name'=>'subject permissions']);
                // create roles
                $userRole = Role::create(['name'=>'user'])->syncPermissions([
                    $miscPermission,
                ]);

                $superAdminRole = Role::create(['name'=>'super-admin'])->syncPermissions([
                    $userPermission1,
                    $userPermission2,
                    $userPermission3,
                    $userPermission4,
                    $rolePermission1,
                    $rolePermission2,
                    $rolePermission3,
                    $rolePermission4,
                    $permission1,
                    $permission2,
                    $permission3,
                    $permission4,
                    $adminPermission1,
                    $subjectPermission1,
                    $seriePermission1,
                    $audioPermission1,
                    $placement_testPermission1,
                    $T_schedulesPermission1,
                    $teacherPermission1,
                    $C_schedulesPermission1,
                    $classroomPermission1,
                    $studentPermission1,
                    $mediaPermission1,
                    $examPermission1,
                    $coursePermission1,
                ]);

                $adminRole = Role::create(['name'=>'admin'])->syncPermissions([

                ]);

                User::create([
                    'role_id' =>2,
                    'name' => 'super admin',
                    'email' => 'super@admin.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),

                ])->assignRole($superAdminRole);

                User::create([
                    'role_id' =>3,
                    'name' => 'admin',
                    'email' => 'admin@admin.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),

                ])->assignRole($adminRole);

    }
}
