<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VitalSignPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vital Sign Types Menu Permission
        Permission::createOrFirst([
            'name' => 'show-vital-sign-types-menu',
            'name_dr' => 'نمایش مینو نوعیت علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        // Vital Sign Types CRUD Permissions
        Permission::createOrFirst([
            'name' => 'view-vital-sign-types',
            'name_dr' => 'نمایش نوعیت علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'create-vital-sign-types',
            'name_dr' => 'ایجاد نوعیت علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'update-vital-sign-types',
            'name_dr' => 'تصحیح نوعیت علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'delete-vital-sign-types',
            'name_dr' => 'حذف نوعیت علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'restore-vital-sign-types',
            'name_dr' => 'بازیابی نوعیت علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'force-delete-vital-sign-types',
            'name_dr' => 'حذف دائمی نوعیت علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        // Vital Signs Menu Permission
        Permission::createOrFirst([
            'name' => 'show-vital-signs-menu',
            'name_dr' => 'نمایش مینو علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        // Vital Signs CRUD Permissions
        Permission::createOrFirst([
            'name' => 'view-vital-signs',
            'name_dr' => 'نمایش علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'create-vital-signs',
            'name_dr' => 'ایجاد علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'update-vital-signs',
            'name_dr' => 'تصحیح علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'delete-vital-signs',
            'name_dr' => 'حذف علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'restore-vital-signs',
            'name_dr' => 'بازیابی علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'force-delete-vital-signs',
            'name_dr' => 'حذف دائمی علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        // Vital Sign Schedules Menu Permission
        Permission::createOrFirst([
            'name' => 'show-vital-sign-schedules-menu',
            'name_dr' => 'نمایش مینو برنامه علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        // Vital Sign Schedules CRUD Permissions
        Permission::createOrFirst([
            'name' => 'view-vital-sign-schedules',
            'name_dr' => 'نمایش برنامه علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'create-vital-sign-schedules',
            'name_dr' => 'ایجاد برنامه علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'update-vital-sign-schedules',
            'name_dr' => 'تصحیح برنامه علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'delete-vital-sign-schedules',
            'name_dr' => 'حذف برنامه علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'restore-vital-sign-schedules',
            'name_dr' => 'بازیابی برنامه علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'force-delete-vital-sign-schedules',
            'name_dr' => 'حذف دائمی برنامه علائم حیاتی',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);
    }
}
