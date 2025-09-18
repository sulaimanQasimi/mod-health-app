<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::createOrFirst([
            'name' => 'show-information-menu',
            'name_dr' => 'نمایش مینو پذیرش',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'show-my-visits-menu',
            'name_dr' => 'نمایش مینو ملاقات های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'show-my-consultations-menu',
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-prescriptions-menu',
            'name_dr' => 'نمایش مینو نسخه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-hospitalizations-menu',
            'name_dr' => 'نمایش مینو مریضان بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-labs-menu',
            'name_dr' => 'نمایش مینو معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-icu-menu',
            'name_dr' => 'نمایش مینو ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-anesthesias-menu',
            'name_dr' => 'نمایش مینو انستیزی ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-operations-menu',
            'name_dr' => 'نمایش مینو عملیات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-settings-menu',
            'name_dr' => 'نمایش مینو تنظیمات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 09:35:32'
        ]);

        Permission::createOrFirst([
            'name' => 'show-users-menu',
            'name_dr' => 'نمایش مینو کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:01:51'
        ]);

        Permission::createOrFirst([
            'name' => 'show-roles-menu',
            'name_dr' => 'نمایش مینو نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:01'
        ]);

        Permission::createOrFirst([
            'name' => 'show-permissions-menu',
            'name_dr' => 'نمایش مینو صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:14'
        ]);

        Permission::createOrFirst([
            'name' => 'show-relations-menu',
            'name_dr' => 'مینو ارتباط خانواده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:02:28'
        ]);

        Permission::createOrFirst([
            'name' => 'create-users',
            'name_dr' => 'ایجاد کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:39'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-users',
            'name_dr' => 'تصحیح کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-roles',
            'name_dr' => 'ایجاد نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:05'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-roles',
            'name_dr' => 'تصحیح نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:14'
        ]);

        Permission::createOrFirst([
            'name' => 'create-permissions',
            'name_dr' => 'ایجاد صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:25'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-permissions',
            'name_dr' => 'تصحیح صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:10:40'
        ]);

        Permission::createOrFirst([
            'name' => 'deactivate-users',
            'name_dr' => 'غیرفعال نمودن یوزر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-departments-menu',
            'name_dr' => 'نمایش مینو دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-sections-menu',
            'name_dr' => 'نمایش مینو بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-floors-menu',
            'name_dr' => 'نمایش مینو منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-rooms-menu',
            'name_dr' => 'نمایش مینو اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-beds-menu',
            'name_dr' => 'نمایش مینو بستر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-labs-types-menu',
            'name_dr' => 'نمایش مینو نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-test-types-menu',
            'name_dr' => 'نمایش مینو نوعیت تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-operation-types-menu',
            'name_dr' => 'نمایش مینو نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-branches-menu',
            'name_dr' => 'نمایش مینو شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-reports-menu',
            'name_dr' => 'نمایش مینو راپورها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-medicine-types-menu',
            'name_dr' => 'نمایش مینو نوعیت ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-medicine-menu',
            'name_dr' => 'نمایش مینو ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-under-review-menu',
            'name_dr' => 'نمایش مینو مریضان تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-food-types-menu',
            'name_dr' => 'نمایش مینو نوعیت غذا',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-blood-bank-menu',
            'name_dr' => 'نمایش مینو بانک خون',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-pacu-menu',
            'name_dr' => 'نمایش مینو PACU ',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-add-icu-procedures-menu',
            'name_dr' => 'نمایش مینو پروسیجر های ICU ',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-medicine-usage-menu',
            'name_dr' => 'نمایش مینو طروق تطبیق ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'show-disease-menu',
            'name_dr' => 'نمایش مینو امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:36'
        ]);

        Permission::createOrFirst([
            'name' => 'create-relations',
            'name_dr' => 'ایجاد ارتباط خانواده گی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-relations',
            'name_dr' => 'تصحیح ارتباط خانواده گی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-relations',
            'name_dr' => 'حذف ارتباط خانواده گی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-departments',
            'name_dr' => 'ایجاد دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-departments',
            'name_dr' => 'تصحیح دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-departments',
            'name_dr' => 'حذف دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-sections',
            'name_dr' => 'ایجاد بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-sections',
            'name_dr' => 'تصحیح بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-sections',
            'name_dr' => 'حذف بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-floors',
            'name_dr' => 'ایجاد منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-floors',
            'name_dr' => 'تصحیح منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-floors',
            'name_dr' => 'حذف منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-rooms',
            'name_dr' => 'ایجاد اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-rooms',
            'name_dr' => 'تصحیح اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-rooms',
            'name_dr' => 'حذف اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'show-rooms',
            'name_dr' => 'نمایش اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-beds',
            'name_dr' => 'ایجاد بسترها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-beds',
            'name_dr' => 'تصحیح بسترها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-beds',
            'name_dr' => 'حذف بسترها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-lab-types',
            'name_dr' => 'ایجاد نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-lab-types',
            'name_dr' => 'تصحیح نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-lab-types',
            'name_dr' => 'حذف نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-labs',
            'name_dr' => 'ایجاد تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-labs',
            'name_dr' => 'تصحیح تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-labs',
            'name_dr' => 'حذف تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-procedure-types',
            'name_dr' => 'ایجاد نوعیت پروسیجر های ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-procedure-types',
            'name_dr' => 'تصحیح نوعیت پروسیجر های ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-procedure-types',
            'name_dr' => 'حذف نوعیت پروسیجر های ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-operation-types',
            'name_dr' => 'ایجاد نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-operation-types',
            'name_dr' => 'تصحیح نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-operation-types',
            'name_dr' => 'حذف نوعیت عملیات ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-medicine-types',
            'name_dr' => 'ایجاد نوعیت ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-medicine-types',
            'name_dr' => 'تصحیح نوعیت ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-medicine-types',
            'name_dr' => 'حذف نوعیت ادویه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-medicines',
            'name_dr' => 'ایجاد ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-medicines',
            'name_dr' => 'تصحیح ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-medicines',
            'name_dr' => 'حذف ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-medicines-usage-types',
            'name_dr' => 'ایجاد طروق تطبیق ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-medicines-usage-types',
            'name_dr' => 'تصحیح طروق تطبیق ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-medicines-usage-types',
            'name_dr' => 'حذف طروق تطبیق ادویه جات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-hospitalization-foods',
            'name_dr' => 'ایجاد نوعیت غذا های بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-hospitalization-foods',
            'name_dr' => 'تصحیح نوعیت غذا های بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-hospitalization-foods',
            'name_dr' => 'حذف نوعیت غذا های بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-diseases',
            'name_dr' => 'ایجاد امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-diseases',
            'name_dr' => 'تصحیح امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-diseases',
            'name_dr' => 'حذف امراض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-branches',
            'name_dr' => 'ایجاد شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-branches',
            'name_dr' => 'تصحیح شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-branches',
            'name_dr' => 'حذف شفاخانه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-recipients',
            'name_dr' => 'ایجاد ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:04'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-recipients',
            'name_dr' => 'تصحیح ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'show-recipients-menu',
            'name_dr' => 'تصحیح ادارات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-patients',
            'name_dr' => 'تصحیح مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'print-patient-card',
            'name_dr' => 'چاپ کارت مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'create-appointment',
            'name_dr' => 'ایجاد ملاقات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'upload-patient-image',
            'name_dr' => 'آپلود نمود عکس مریض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'update-appointment-status',
            'name_dr' => 'تصحیح حالت ملاقات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'add-diagnose',
            'name_dr' => 'اضافه نمودن تشخیص',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'add-prescription',
            'name_dr' => 'اضافه نمودن نسخه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'add-advice',
            'name_dr' => 'اضافه نمودن توصیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'add-patient-labs',
            'name_dr' => 'اضافه نمودن معاینات به مریض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'add-consultations',
            'name_dr' => 'اضافه نمودن مشوره ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'refer-to-another-doctor',
            'name_dr' => 'معرفی مریض به داکتر/دیپارتمنت دیگر',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'patient-under-review',
            'name_dr' => 'معرفی مریض به تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'patient-hospitalization',
            'name_dr' => 'بستر نمودن مریض',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'refer-to-anesthesia',
            'name_dr' => 'معرفی مریض به اناستیزی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'refer-to-icu',
            'name_dr' => 'معرفی مریض به ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-diagnoses',
            'name_dr' => 'تصحیح تشخیص ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-diagnoses',
            'name_dr' => 'حذف تشخیص ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-advices',
            'name_dr' => 'تصحیح توصیه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-advices',
            'name_dr' => 'حذف توصیه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-consultations',
            'name_dr' => 'تصحیح مشوره ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-consultations',
            'name_dr' => 'حذف مشوره ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-under-reviews',
            'name_dr' => 'تصحیح تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-under-reviews',
            'name_dr' => 'حذف تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-hospitalizations',
            'name_dr' => 'تصحیح بستر مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-hospitalizations',
            'name_dr' => 'حذف بستر مریضان',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-anesthesias',
            'name_dr' => 'تصحیح انستیزی ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-anesthesias',
            'name_dr' => 'حذف انستیزی ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-icus',
            'name_dr' => 'تصحیح ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-icus',
            'name_dr' => 'حذف ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-prescription',
            'name_dr' => 'تصحیح نمودن نسخه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-prescription',
            'name_dr' => 'حذف نمودن نسخه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-lab-items',
            'name_dr' => 'تصحیح نمودن معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-lab-items',
            'name_dr' => 'حذف نمودن معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-under-review-visit',
            'name_dr' => 'تصحیح نمودن بازدید تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-under-review-visit',
            'name_dr' => 'حذف نمودن بازدید تحت مشاهده',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-daily-icu-progress',
            'name_dr' => 'تصحیح پیشرفت روزمره ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'edit-icu-procedure',
            'name_dr' => 'تصحیح پروسیجر ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);
        Permission::createOrFirst([
            'name' => 'delete-icu-procedure',
            'name_dr' => 'حذف پروسیجر ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:12:13'
        ]);

        //Militery Types        
        Permission::createOrFirst([
            'name' => 'show-militery-types',
            'name_dr' => 'نمایش رتبه نظامی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);


        Permission::createOrFirst([
            'name' => 'create-militery-types',
            'name_dr' => 'ایجاد رتبه نظامی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-militery-types',
            'name_dr' => 'تصحیح رتبه نظامی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-militery-types',
            'name_dr' => 'حذف رتبه نظامی',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        // Nurse Permissions
        Permission::createOrFirst([
            'name' => 'show-nurses-menu',
            'name_dr' => 'نمایش مینو پرستاران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'view-nurses',
            'name_dr' => 'نمایش پرستاران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-nurses',
            'name_dr' => 'ایجاد پرستاران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-nurses',
            'name_dr' => 'تصحیح پرستاران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-nurses',
            'name_dr' => 'حذف پرستاران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'restore-nurses',
            'name_dr' => 'بازیابی پرستاران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'force-delete-nurses',
            'name_dr' => 'حذف دائمی پرستاران',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        // Diabetes Chart Permissions
        Permission::createOrFirst([
            'name' => 'show-diabetes-charts-menu',
            'name_dr' => 'نمایش مینو چارت دیابت',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'view-diabetes-charts',
            'name_dr' => 'نمایش چارت دیابت',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-diabetes-charts',
            'name_dr' => 'ایجاد چارت دیابت',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-diabetes-charts',
            'name_dr' => 'تصحیح چارت دیابت',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-diabetes-charts',
            'name_dr' => 'حذف چارت دیابت',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'restore-diabetes-charts',
            'name_dr' => 'بازیابی چارت دیابت',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'force-delete-diabetes-charts',
            'name_dr' => 'حذف دائمی چارت دیابت',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        // Nurse Notes Permissions
        Permission::createOrFirst([
            'name' => 'show-nurse-notes-menu',
            'name_dr' => 'نمایش مینو یادداشت پرستار',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'view-nurse-notes',
            'name_dr' => 'نمایش یادداشت پرستار',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create-nurse-notes',
            'name_dr' => 'ایجاد یادداشت پرستار',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit-nurse-notes',
            'name_dr' => 'تصحیح یادداشت پرستار',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete-nurse-notes',
            'name_dr' => 'حذف یادداشت پرستار',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'restore-nurse-notes',
            'name_dr' => 'بازیابی یادداشت پرستار',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'force-delete-nurse-notes',
            'name_dr' => 'حذف دائمی یادداشت پرستار',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        // Nutrition Care Permissions
        Permission::createOrFirst([
            'name' => 'show-nutrition-care-menu',
            'name_dr' => 'نمایش مینو مراقبت تغذیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'view_nutrition_care',
            'name_dr' => 'نمایش مراقبت تغذیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'create_nutrition_care',
            'name_dr' => 'ایجاد مراقبت تغذیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'edit_nutrition_care',
            'name_dr' => 'تصحیح مراقبت تغذیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'delete_nutrition_care',
            'name_dr' => 'حذف مراقبت تغذیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'restore_nutrition_care',
            'name_dr' => 'بازیابی مراقبت تغذیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);

        Permission::createOrFirst([
            'name' => 'force_delete_nutrition_care',
            'name_dr' => 'حذف دائمی مراقبت تغذیه',
            'name_pa' => NULL,
            'guard_name' => 'web',
            'created_at' => '2023-08-22 14:05:43',
            'updated_at' => '2023-08-22 10:09:52'
        ]);


        // Nursing Assessment Menu Permission
        Permission::createOrFirst([
            'name' => 'show-nursing-assessments-menu',
            'name_dr' => 'نمایش مینو ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        // Nursing Assessment CRUD Permissions
        Permission::createOrFirst([
            'name' => 'view_nursing_assessment',
            'name_dr' => 'نمایش ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'create_nursing_assessment',
            'name_dr' => 'ایجاد ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'edit_nursing_assessment',
            'name_dr' => 'تصحیح ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'delete_nursing_assessment',
            'name_dr' => 'حذف ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'restore_nursing_assessment',
            'name_dr' => 'بازیابی ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::createOrFirst([
            'name' => 'force_delete_nursing_assessment',
            'name_dr' => 'حذف دائمی ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        // Print permission
        Permission::createOrFirst([
            'name' => 'print_nursing_assessment',
            'name_dr' => 'چاپ ارزیابی نرسنگ',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);
        $this->call(PhysiotherapyPermissionSeeder::class);
        $this->call(PharmacyPermissionSeeder::class);
        $this->call(NursingAssessmentPermissionSeeder::class);
        $this->call(NursingAssessmentRolePermissionSeeder::class);
        $this->call(PhysiotherapyTypeSeeder::class);
        $this->call(PhysiotherapyProcedureReviewSeeder::class);
        User::find(1)->givePermissionTo(Permission::all());

    }
}
