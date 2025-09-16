<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NursingAssessmentRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the nursing assessment permissions
        $nursingAssessmentPermissions = Permission::whereIn('name', [
            'show-nursing-assessments-menu',
            'view_nursing_assessment',
            'create_nursing_assessment',
            'edit_nursing_assessment',
            'delete_nursing_assessment',
            'restore_nursing_assessment',
            'force_delete_nursing_assessment',
            'print_nursing_assessment',
        ])->get();

        // Super Admin (ID: 1) - Gets all permissions
        $superAdminRole = Role::find(1);
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($nursingAssessmentPermissions);
        }

        // Admin (ID: 2) - Gets all permissions
        $adminRole = Role::find(2);
        if ($adminRole) {
            $adminRole->givePermissionTo($nursingAssessmentPermissions);
        }

        // HR (ID: 3) - Gets view, create, edit, delete permissions
        $hrRole = Role::find(3);
        if ($hrRole) {
            $hrPermissions = Permission::whereIn('name', [
                'show-nursing-assessments-menu',
                'view_nursing_assessment',
                'create_nursing_assessment',
                'edit_nursing_assessment',
                'delete_nursing_assessment',
                'print_nursing_assessment',
            ])->get();
            $hrRole->givePermissionTo($hrPermissions);
        }

        // Nurse (ID: 10) - Gets view, create, edit, delete, print permissions
        $nurseRole = Role::find(10);
        if ($nurseRole) {
            $nursePermissions = Permission::whereIn('name', [
                'show-nursing-assessments-menu',
                'view_nursing_assessment',
                'create_nursing_assessment',
                'edit_nursing_assessment',
                'delete_nursing_assessment',
                'print_nursing_assessment',
            ])->get();
            $nurseRole->givePermissionTo($nursePermissions);
        }

        // Doctor (ID: 4) - Gets view and print permissions only
        $doctorRole = Role::find(4);
        if ($doctorRole) {
            $doctorPermissions = Permission::whereIn('name', [
                'show-nursing-assessments-menu',
                'view_nursing_assessment',
                'print_nursing_assessment',
            ])->get();
            $doctorRole->givePermissionTo($doctorPermissions);
        }
    }
}