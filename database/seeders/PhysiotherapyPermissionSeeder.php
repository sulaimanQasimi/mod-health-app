<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhysiotherapyPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Physiotherapy Type permissions
        Permission::firstOrCreate([
            'name' => 'show-physiotherapy-types',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش انواع فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'create-physiotherapy-types',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'ایجاد نوع فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'edit-physiotherapy-types',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'ویرایش نوع فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'delete-physiotherapy-types',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'حذف نوع فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        // Physiotherapy Procedure permissions
        Permission::firstOrCreate([
            'name' => 'show-physiotherapy-procedures',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش روش های فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'show-own-physiotherapy-procedures',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش روش های فیزیوتراپی خود',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'create-physiotherapy-procedures',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'ایجاد روش فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'edit-physiotherapy-procedures',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'ویرایش روش فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'delete-physiotherapy-procedures',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'حذف روش فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        // Physiotherapy menu permissions
        Permission::firstOrCreate([
            'name' => 'show-physiotherapy-menu',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش مینو فیزیوتراپی',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'show-physiotherapy-reports',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش گزارشات فیزیوتراپی',
            'name_pa' => NULL,
        ]);
    }
}
