<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PharmacyPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pharmacy permissions
        Permission::firstOrCreate([
            'name' => 'pharmacy.index',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش دواخانه ها',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'pharmacy.create',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'ایجاد دواخانه',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'pharmacy.edit',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'ویرایش دواخانه',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'pharmacy.delete',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'حذف دواخانه',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'pharmacy.show',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش جزئیات دواخانه',
            'name_pa' => NULL,
        ]);

        Permission::firstOrCreate([
            'name' => 'show-pharmacy-menu',
            'guard_name' => 'web',
        ], [
            'name_dr' => 'نمایش مینو دواخانه',
            'name_pa' => NULL,
        ]);
    }
}
