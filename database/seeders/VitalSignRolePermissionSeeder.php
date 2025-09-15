<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VitalSignRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the vital sign permissions
        $vitalSignPermissions = Permission::whereIn('name', [
            'show-vital-sign-types-menu',
            'view-vital-sign-types',
            'create-vital-sign-types',
            'update-vital-sign-types',
            'delete-vital-sign-types',
            'restore-vital-sign-types',
            'force-delete-vital-sign-types',
            'show-vital-signs-menu',
            'view-vital-signs',
            'create-vital-signs',
            'update-vital-signs',
            'delete-vital-signs',
            'restore-vital-signs',
            'force-delete-vital-signs',
            'show-vital-sign-schedules-menu',
            'view-vital-sign-schedules',
            'create-vital-sign-schedules',
            'update-vital-sign-schedules',
            'delete-vital-sign-schedules',
            'restore-vital-sign-schedules',
            'force-delete-vital-sign-schedules',
        ])->get();

        // Super Admin (ID: 1) - Gets all permissions
        $superAdminRole = Role::find(1);
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($vitalSignPermissions);
        }

        // Nurse (ID: 10) - Gets view, create, update permissions for vital signs and schedules
        $nurseRole = Role::find(10);
        if ($nurseRole) {
            $nursePermissions = $vitalSignPermissions->whereIn('name', [
                'show-vital-sign-types-menu',
                'view-vital-sign-types',
                'show-vital-signs-menu',
                'view-vital-signs',
                'create-vital-signs',
                'update-vital-signs',
                'show-vital-sign-schedules-menu',
                'view-vital-sign-schedules',
                'create-vital-sign-schedules',
                'update-vital-sign-schedules',
            ]);
            $nurseRole->givePermissionTo($nursePermissions);
        }

        // Doctor (ID: 8) - Gets view, create, update permissions for vital signs and schedules
        $doctorRole = Role::find(8);
        if ($doctorRole) {
            $doctorPermissions = $vitalSignPermissions->whereIn('name', [
                'show-vital-sign-types-menu',
                'view-vital-sign-types',
                'show-vital-signs-menu',
                'view-vital-signs',
                'create-vital-signs',
                'update-vital-signs',
                'show-vital-sign-schedules-menu',
                'view-vital-sign-schedules',
                'create-vital-sign-schedules',
                'update-vital-sign-schedules',
            ]);
            $doctorRole->givePermissionTo($doctorPermissions);
        }
    }
}
