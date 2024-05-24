<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([
            'id' => 1,
            'name' => 'show-information-menu',
            'name_dr' => 'نمایش مینو پذیرش',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 2,
            'name' => 'show-my-visits-menu',
            'name_dr' => 'نمایش مینو ملاقات های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 3,
            'name' => 'show-my-consultations-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 4,
            'name' => 'show-prescriptions-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 5,
            'name' => 'show-hospitalizations-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 6,
            'name' => 'show-labs-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 7,
            'name' => 'show-icu-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 8,
            'name' => 'show-icu-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 9,
            'name' => 'show-anesthesias-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 10,
            'name' => 'show-operations-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 11,
            'name' => 'show-settings-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 12,
            'name' => 'show-users-menu',
            'name_dr' => 'نمایش مینو کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:01:51'
        ]);

        Permission::create([
            'id' => 13,
            'name' => 'show-roles-menu',
            'name_dr' => 'نمایش مینو نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:01'
        ]);

        Permission::create([
            'id' => 14,
            'name' => 'show-permissions-menu',
            'name_dr' => 'نمایش مینو صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:14'
        ]);

        Permission::create([
            'id' => 15,
            'name' => 'show-relations-menu',
            'name_dr' => 'مینو ارتباط خانواده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:28'
        ]);

        Permission::create([
            'id' => 16,
            'name' => 'create-users',
            'name_dr' => 'ایجاد کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:39'
        ]);

        Permission::create([
            'id' => 17,
            'name' => 'edit-users',
            'name_dr' => 'تصحیح کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 18,
            'name' => 'create-roles',
            'name_dr' => 'ایجاد نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:05'
        ]);

        Permission::create([
            'id' => 19,
            'name' => 'edit-roles',
            'name_dr' => 'تصحیح نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:14'
        ]);

        Permission::create([
            'id' => 20,
            'name' => 'create-permissions',
            'name_dr' => 'ایجاد صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:25'
        ]);

        Permission::create([
            'id' => 21,
            'name' => 'edit-permissions',
            'name_dr' => 'تصحیح صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:40'
        ]);
        Permission::create([
            'id' => 22,
            'name' => 'create-recipients',
            'name_dr' => 'ایجاد ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:04'
        ]);

        Permission::create([
            'id' => 23,
            'name' => 'edit-recipients',
            'name_dr' => 'تصحیح ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::create([
            'id' => 24,
            'name' => 'deactivate-users',
            'name_dr' => 'غیرفعال نمودن یوزر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 24,
            'name' => 'show-departments-menu',
            'name_dr' => 'غیرفعال نمودن یوزر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 24,
            'name' => 'show-sections-menu',
            'name_dr' => 'غیرفعال نمودن یوزر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);
    }
}
