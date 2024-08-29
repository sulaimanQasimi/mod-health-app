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
            'name_dr' => 'نمایش مینو نسخه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 5,
            'name' => 'show-hospitalizations-menu',
            'name_dr' => 'نمایش مینو مریضان بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 6,
            'name' => 'show-labs-menu',
            'name_dr' => 'نمایش مینو معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 7,
            'name' => 'show-icu-menu',
            'name_dr' => 'نمایش مینو ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 8,
            'name' => 'show-anesthesias-menu',
            'name_dr' => 'نمایش مینو انستیزی ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 9,
            'name' => 'show-operations-menu',
            'name_dr' => 'نمایش مینو عملیات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 10,
            'name' => 'show-settings-menu',
            'name_dr' => 'نمایش مینو تنظیمات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::create([
            'id' => 11,
            'name' => 'show-users-menu',
            'name_dr' => 'نمایش مینو کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:01:51'
        ]);

        Permission::create([
            'id' => 12,
            'name' => 'show-roles-menu',
            'name_dr' => 'نمایش مینو نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:01'
        ]);

        Permission::create([
            'id' => 13,
            'name' => 'show-permissions-menu',
            'name_dr' => 'نمایش مینو صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:14'
        ]);

        Permission::create([
            'id' => 14,
            'name' => 'show-relations-menu',
            'name_dr' => 'مینو ارتباط خانواده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:28'
        ]);

        Permission::create([
            'id' => 15,
            'name' => 'create-users',
            'name_dr' => 'ایجاد کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:39'
        ]);

        Permission::create([
            'id' => 16,
            'name' => 'edit-users',
            'name_dr' => 'تصحیح کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 17,
            'name' => 'create-roles',
            'name_dr' => 'ایجاد نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:05'
        ]);

        Permission::create([
            'id' => 18,
            'name' => 'edit-roles',
            'name_dr' => 'تصحیح نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:14'
        ]);

        Permission::create([
            'id' => 19,
            'name' => 'create-permissions',
            'name_dr' => 'ایجاد صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:25'
        ]);

        Permission::create([
            'id' => 20,
            'name' => 'edit-permissions',
            'name_dr' => 'تصحیح صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:40'
        ]);
        Permission::create([
            'id' => 21,
            'name' => 'create-recipients',
            'name_dr' => 'ایجاد ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:04'
        ]);

        Permission::create([
            'id' => 22,
            'name' => 'edit-recipients',
            'name_dr' => 'تصحیح ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::create([
            'id' => 23,
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
            'name_dr' => 'نمایش مینو دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 25,
            'name' => 'show-sections-menu',
            'name_dr' => 'نمایش مینو بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 26,
            'name' => 'show-floors-menu',
            'name_dr' => 'نمایش مینو منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 27,
            'name' => 'show-rooms-menu',
            'name_dr' => 'نمایش مینو اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 28,
            'name' => 'show-beds-menu',
            'name_dr' => 'نمایش مینو بستر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 29,
            'name' => 'show-labs-types-menu',
            'name_dr' => 'نمایش مینو نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 30,
            'name' => 'show-test-types-menu',
            'name_dr' => 'نمایش مینو نوعیت تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 31,
            'name' => 'show-operation-types-menu',
            'name_dr' => 'نمایش مینو نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 32,
            'name' => 'show-branches-menu',
            'name_dr' => 'نمایش مینو شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 33,
            'name' => 'show-reports-menu',
            'name_dr' => 'نمایش مینو راپورها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 34,
            'name' => 'show-medicine-types-menu',
            'name_dr' => 'نمایش مینو نوعیت ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 35,
            'name' => 'show-medicine-menu',
            'name_dr' => 'نمایش مینو ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 36,
            'name' => 'show-under-review-menu',
            'name_dr' => 'نمایش مینو مریضان تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 37,
            'name' => 'show-food-types-menu',
            'name_dr' => 'نمایش مینو نوعیت غذا',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 38,
            'name' => 'show-blood-bank-menu',
            'name_dr' => 'نمایش مینو بانک خون',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 39,
            'name' => 'show-pacu-menu',
            'name_dr' => 'نمایش مینو PACU ',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 40,
            'name' => 'show-add-icu-procedures-menu',
            'name_dr' => 'نمایش مینو پروسیجر های ICU ',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 41,
            'name' => 'show-medicine-usage-menu',
            'name_dr' => 'نمایش مینو طروق تطبیق ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 42,
            'name' => 'show-disease-menu',
            'name_dr' => 'نمایش مینو امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);


    }
}
