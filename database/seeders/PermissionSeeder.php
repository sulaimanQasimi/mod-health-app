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
            'name' => 'deactivate-users',
            'name_dr' => 'غیرفعال نمودن یوزر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 22,
            'name' => 'show-departments-menu',
            'name_dr' => 'نمایش مینو دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 23,
            'name' => 'show-sections-menu',
            'name_dr' => 'نمایش مینو بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 24,
            'name' => 'show-floors-menu',
            'name_dr' => 'نمایش مینو منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 25,
            'name' => 'show-rooms-menu',
            'name_dr' => 'نمایش مینو اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 26,
            'name' => 'show-beds-menu',
            'name_dr' => 'نمایش مینو بستر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 27,
            'name' => 'show-labs-types-menu',
            'name_dr' => 'نمایش مینو نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 28,
            'name' => 'show-test-types-menu',
            'name_dr' => 'نمایش مینو نوعیت تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 29,
            'name' => 'show-operation-types-menu',
            'name_dr' => 'نمایش مینو نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 30,
            'name' => 'show-branches-menu',
            'name_dr' => 'نمایش مینو شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 31,
            'name' => 'show-reports-menu',
            'name_dr' => 'نمایش مینو راپورها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 32,
            'name' => 'show-medicine-types-menu',
            'name_dr' => 'نمایش مینو نوعیت ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 33,
            'name' => 'show-medicine-menu',
            'name_dr' => 'نمایش مینو ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 34,
            'name' => 'show-under-review-menu',
            'name_dr' => 'نمایش مینو مریضان تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 35,
            'name' => 'show-food-types-menu',
            'name_dr' => 'نمایش مینو نوعیت غذا',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 36,
            'name' => 'show-blood-bank-menu',
            'name_dr' => 'نمایش مینو بانک خون',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 37,
            'name' => 'show-pacu-menu',
            'name_dr' => 'نمایش مینو PACU ',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 38,
            'name' => 'show-add-icu-procedures-menu',
            'name_dr' => 'نمایش مینو پروسیجر های ICU ',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 39,
            'name' => 'show-medicine-usage-menu',
            'name_dr' => 'نمایش مینو طروق تطبیق ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 40,
            'name' => 'show-disease-menu',
            'name_dr' => 'نمایش مینو امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::create([
            'id' => 41,
            'name' => 'create-relations',
            'name_dr' => 'ایجاد ارتباط خانواده گی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 42,
            'name' => 'edit-relations',
            'name_dr' => 'تصحیح ارتباط خانواده گی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 43,
            'name' => 'delete-relations',
            'name_dr' => 'حذف ارتباط خانواده گی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 44,
            'name' => 'create-departments',
            'name_dr' => 'ایجاد دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 45,
            'name' => 'edit-departments',
            'name_dr' => 'تصحیح دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 46,
            'name' => 'delete-departments',
            'name_dr' => 'حذف دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 47,
            'name' => 'create-sections',
            'name_dr' => 'ایجاد بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 48,
            'name' => 'edit-sections',
            'name_dr' => 'تصحیح بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 49,
            'name' => 'delete-sections',
            'name_dr' => 'حذف بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 50,
            'name' => 'create-floors',
            'name_dr' => 'ایجاد منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 51,
            'name' => 'edit-floors',
            'name_dr' => 'تصحیح منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 52,
            'name' => 'delete-floors',
            'name_dr' => 'حذف منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 53,
            'name' => 'create-rooms',
            'name_dr' => 'ایجاد اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 54,
            'name' => 'edit-rooms',
            'name_dr' => 'تصحیح اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 55,
            'name' => 'delete-rooms',
            'name_dr' => 'حذف اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 56,
            'name' => 'show-rooms',
            'name_dr' => 'نمایش اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 57,
            'name' => 'create-beds',
            'name_dr' => 'ایجاد بسترها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 58,
            'name' => 'edit-beds',
            'name_dr' => 'تصحیح بسترها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 59,
            'name' => 'delete-beds',
            'name_dr' => 'حذف بسترها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 60,
            'name' => 'create-lab-types',
            'name_dr' => 'ایجاد نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 61,
            'name' => 'edit-lab-types',
            'name_dr' => 'تصحیح نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 62,
            'name' => 'delete-lab-types',
            'name_dr' => 'حذف نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 63,
            'name' => 'create-labs',
            'name_dr' => 'ایجاد تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 64,
            'name' => 'edit-labs',
            'name_dr' => 'تصحیح تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 65,
            'name' => 'delete-labs',
            'name_dr' => 'حذف تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 66,
            'name' => 'create-procedure-types',
            'name_dr' => 'ایجاد نوعیت پروسیجر های ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 67,
            'name' => 'edit-procedure-types',
            'name_dr' => 'تصحیح نوعیت پروسیجر های ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 68,
            'name' => 'delete-procedure-types',
            'name_dr' => 'حذف نوعیت پروسیجر های ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 69,
            'name' => 'create-operation-types',
            'name_dr' => 'ایجاد نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 70,
            'name' => 'edit-operation-types',
            'name_dr' => 'تصحیح نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 71,
            'name' => 'delete-operation-types',
            'name_dr' => 'حذف نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 72,
            'name' => 'create-medicine-types',
            'name_dr' => 'ایجاد نوعیت ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 73,
            'name' => 'edit-medicine-types',
            'name_dr' => 'تصحیح نوعیت ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 74,
            'name' => 'delete-medicine-types',
            'name_dr' => 'حذف نوعیت ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 75,
            'name' => 'create-medicines',
            'name_dr' => 'ایجاد ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 76,
            'name' => 'edit-medicines',
            'name_dr' => 'تصحیح ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 77,
            'name' => 'delete-medicines',
            'name_dr' => 'حذف ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 78,
            'name' => 'create-medicines-usage-types',
            'name_dr' => 'ایجاد طروق تطبیق ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 79,
            'name' => 'edit-medicines-usage-types',
            'name_dr' => 'تصحیح طروق تطبیق ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 80,
            'name' => 'delete-medicines-usage-types',
            'name_dr' => 'حذف طروق تطبیق ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 81,
            'name' => 'create-hospitalization-foods',
            'name_dr' => 'ایجاد نوعیت غذا های بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 82,
            'name' => 'edit-hospitalization-foods',
            'name_dr' => 'تصحیح نوعیت غذا های بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 83,
            'name' => 'delete-hospitalization-foods',
            'name_dr' => 'حذف نوعیت غذا های بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 84,
            'name' => 'create-diseases',
            'name_dr' => 'ایجاد امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 85,
            'name' => 'edit-diseases',
            'name_dr' => 'تصحیح امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 86,
            'name' => 'delete-diseases',
            'name_dr' => 'حذف امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 87,
            'name' => 'create-branches',
            'name_dr' => 'ایجاد شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 88,
            'name' => 'edit-branches',
            'name_dr' => 'تصحیح شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 89,
            'name' => 'delete-branches',
            'name_dr' => 'حذف شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::create([
            'id' => 90,
            'name' => 'create-recipients',
            'name_dr' => 'ایجاد ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:04'
        ]);

        Permission::create([
            'id' => 91,
            'name' => 'edit-recipients',
            'name_dr' => 'تصحیح ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 92,
            'name' => 'show-recipients-menu',
            'name_dr' => 'تصحیح ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::create([
            'id' => 93,
            'name' => 'edit-patients',
            'name_dr' => 'تصحیح مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 94,
            'name' => 'print-patient-card',
            'name_dr' => 'چاپ کارت مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 95,
            'name' => 'create-appointment',
            'name_dr' => 'ایجاد ملاقات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 96,
            'name' => 'upload-patient-image',
            'name_dr' => 'آپلود نمود عکس مریض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 97,
            'name' => 'update-appointment-status',
            'name_dr' => 'تصحیح حالت ملاقات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 98,
            'name' => 'add-diagnose',
            'name_dr' => 'اضافه نمودن تشخیص',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 99,
            'name' => 'add-prescription',
            'name_dr' => 'اضافه نمودن نسخه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 100,
            'name' => 'add-advice',
            'name_dr' => 'اضافه نمودن توصیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 101,
            'name' => 'add-patient-labs',
            'name_dr' => 'اضافه نمودن معاینات به مریض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 102,
            'name' => 'add-consultations',
            'name_dr' => 'اضافه نمودن مشوره ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 103,
            'name' => 'refer-to-another-doctor',
            'name_dr' => 'معرفی مریض به داکتر/دیپارتمنت دیگر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 104,
            'name' => 'patient-under-review',
            'name_dr' => 'معرفی مریض به تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 105,
            'name' => 'patient-hospitalization',
            'name_dr' => 'بستر نمودن مریض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 106,
            'name' => 'refer-to-anesthesia',
            'name_dr' => 'معرفی مریض به اناستیزی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 107,
            'name' => 'refer-to-icu',
            'name_dr' => 'معرفی مریض به ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 108,
            'name' => 'edit-diagnoses',
            'name_dr' => 'تصحیح تشخیص ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 109,
            'name' => 'delete-diagnoses',
            'name_dr' => 'حذف تشخیص ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 110,
            'name' => 'edit-advices',
            'name_dr' => 'تصحیح توصیه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 111,
            'name' => 'delete-advices',
            'name_dr' => 'حذف توصیه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 112,
            'name' => 'edit-consultations',
            'name_dr' => 'تصحیح مشوره ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 113,
            'name' => 'delete-consultations',
            'name_dr' => 'حذف مشوره ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 114,
            'name' => 'edit-under-reviews',
            'name_dr' => 'تصحیح تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 115,
            'name' => 'delete-under-reviews',
            'name_dr' => 'حذف تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 116,
            'name' => 'edit-hospitalizations',
            'name_dr' => 'تصحیح بستر مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 117,
            'name' => 'delete-hospitalizations',
            'name_dr' => 'حذف بستر مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 118,
            'name' => 'edit-anesthesias',
            'name_dr' => 'تصحیح انستیزی ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 119,
            'name' => 'delete-anesthesias',
            'name_dr' => 'حذف انستیزی ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 120,
            'name' => 'edit-icus',
            'name_dr' => 'تصحیح ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 121,
            'name' => 'delete-icus',
            'name_dr' => 'حذف ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::create([
            'id' => 122,
            'name' => 'edit-prescription',
            'name_dr' => 'تصحیح نمودن نسخه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::create([
            'id' => 123,
            'name' => 'delete-prescription',
            'name_dr' => 'حذف نمودن نسخه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 124,
            'name' => 'edit-lab-items',
            'name_dr' => 'تصحیح نمودن معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::create([
            'id' => 125,
            'name' => 'delete-lab-items',
            'name_dr' => 'حذف نمودن معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

    }
}
