<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
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
        Permission::updateOrCreate([
            'name' => 'show-information-menu',
        ], [
            'name_dr' => 'نمایش مینو پذیرش',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-my-visits-menu',
        ], [
            'name_dr' => 'نمایش مینو ملاقات های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);   

        Permission::updateOrCreate([
            'name' => 'show-my-consultations-menu',
        ], [
            'name_dr' => 'نمایش مینو مشوره های من',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);   

        Permission::updateOrCreate([
            'name' => 'show-prescriptions-menu',
        ], [
            'name_dr' => 'نمایش مینو نسخه ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);   

        Permission::updateOrCreate([
            'name' => 'show-hospitalizations-menu',
        ], [
            'name_dr' => 'نمایش مینو مریضان بستر',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);   

        Permission::updateOrCreate([
            'name' => 'show-labs-menu',
        ], [
            'name_dr' => 'نمایش مینو معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-icu-menu',
        ], [
            'name_dr' => 'نمایش مینو ICU',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-anesthesias-menu',
        ], [
            'name_dr' => 'نمایش مینو انستیزی ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-operations-menu',
        ], [
            'name_dr' => 'نمایش مینو عملیات',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-settings-menu',
        ], [
            'name_dr' => 'نمایش مینو تنظیمات',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-users-menu',
        ], [
            'name_dr' => 'نمایش مینو کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-roles-menu',
        ], [
            'name_dr' => 'نمایش مینو نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-permissions-menu',
        ], [
                'name_dr' => 'نمایش مینو صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-relations-menu',
        ], [
            'name_dr' => 'مینو ارتباط خانواده',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'create-users',
        ], [
            'name_dr' => 'ایجاد کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'edit-users',
        ], [
            'name_dr' => 'تصحیح کاربران',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'create-roles',
        ], [
            'name_dr' => 'ایجاد نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'edit-roles',
        ], [
            'name_dr' => 'تصحیح نقش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'create-permissions',
        ], [
            'name_dr' => 'ایجاد صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'edit-permissions',
        ], [
                'name_dr' => 'تصحیح صلاحیت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'deactivate-users',
        ], [
            'name_dr' => 'غیرفعال نمودن یوزر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-departments-menu',
        ], [
            'name_dr' => 'نمایش مینو دیپارتمنت ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-sections-menu',
        ], [
            'name_dr' => 'نمایش مینو بخش ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-floors-menu',
        ], [
            'name_dr' => 'نمایش مینو منزل ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-rooms-menu',
        ], [
            'name_dr' => 'نمایش مینو اطاق ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-beds-menu',
        ], [
            'name_dr' => 'نمایش مینو بستر ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-labs-types-menu',
        ], [
            'name_dr' => 'نمایش مینو نوعیت معاینات',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate([
            'name' => 'show-test-types-menu',
        ], [
            'name_dr' => 'نمایش مینو نوعیت تست ها',
            'name_pa' => NULL,
            'guard_name' => 'web',
        ]);

        Permission::updateOrCreate(
            ['name' => 'show-operation-types-menu'],
            [
                'name_dr' => 'نمایش مینو نوعیت عملیات ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-branches-menu'],
            [
                'name_dr' => 'نمایش مینو شفاخانه ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-reports-menu'],
            [
                'name_dr' => 'نمایش مینو راپورها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-medicine-types-menu'],
            [
                'name_dr' => 'نمایش مینو نوعیت ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-medicine-menu'],
            [
                'name_dr' => 'نمایش مینو ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-under-review-menu'],
            [
                'name_dr' => 'نمایش مینو مریضان تحت مشاهده',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-food-types-menu'],
            [
                'name_dr' => 'نمایش مینو نوعیت غذا',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-prosthetics-menu'],
            [
                'name_dr' => 'نمایش مینو اندام مصنوعی و ارتزیک',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-prosthetics-catalog'],
            [
                'name_dr' => 'مدیریت کاتالوگ قطعات اندام مصنوعی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-prosthetics-stock'],
            [
                'name_dr' => 'مدیریت انبار اندام مصنوعی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.view'],
            [
                'name_dr' => 'نمایش دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.create'],
            [
                'name_dr' => 'ایجاد دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.update'],
            [
                'name_dr' => 'ویرایش دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.delete'],
            [
                'name_dr' => 'حذف دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.request.create'],
            [
                'name_dr' => 'ایجاد درخواست دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.request.approve'],
            [
                'name_dr' => 'تایید درخواست دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.request.fulfill'],
            [
                'name_dr' => 'تکمیل درخواست دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.report.export'],
            [
                'name_dr' => 'خروجی گزارش دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.transaction.view'],
            [
                'name_dr' => 'نمایش تراکنش های دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.transaction.create'],
            [
                'name_dr' => 'ثبت تراکنش دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.movement.depot_to_depot'],
            [
                'name_dr' => 'انتقال از دیپو به دیپو',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'depot.movement.depot_to_pharmacy'],
            [
                'name_dr' => 'انتقال از دیپو به دواخانه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-blood-bank-menu'],
            [
                'name_dr' => 'نمایش مینو بانک خون',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-blood-inventory'],
            [
                'name_dr' => 'مدیریت موجودی بانک خون',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'issue-blood'],
            [
                'name_dr' => 'صدور واحد خون',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'receive-blood-units'],
            [
                'name_dr' => 'ثبت واحد خون ورودی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-pacu-menu'],
            [
                'name_dr' => 'نمایش مینو PACU ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'refer-to-pacu'],
            [
                'name_dr' => 'معرفی مریض به PACU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-add-icu-procedures-menu'],
            [
                'name_dr' => 'نمایش مینو پروسیجر های ICU ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-medicine-usage-menu'],
            [
                'name_dr' => 'نمایش مینو طروق تطبیق ادویه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-disease-menu'],
            [
                'name_dr' => 'نمایش مینو امراض',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-relations'],
            [
                'name_dr' => 'ایجاد ارتباط خانواده گی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-relations'],
            [
                'name_dr' => 'تصحیح ارتباط خانواده گی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-relations'],
            [
                'name_dr' => 'حذف ارتباط خانواده گی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-departments'],
            [
                'name_dr' => 'ایجاد دیپارتمنت ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-departments'],
            [
                'name_dr' => 'تصحیح دیپارتمنت ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-departments'],
            [
                'name_dr' => 'حذف دیپارتمنت ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-sections'],
            [
                'name_dr' => 'ایجاد بخش ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-sections'],
            [
                'name_dr' => 'تصحیح بخش ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-sections'],
            [
                'name_dr' => 'حذف بخش ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-floors'],
            [
                'name_dr' => 'ایجاد منزل ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-floors'],
            [
                'name_dr' => 'تصحیح منزل ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-floors'],
            [
                'name_dr' => 'حذف منزل ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-rooms'],
            [
                'name_dr' => 'ایجاد اطاق ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-rooms'],
            [
                'name_dr' => 'تصحیح اطاق ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-rooms'],
            [
                'name_dr' => 'حذف اطاق ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-rooms'],
            [
                'name_dr' => 'نمایش اطاق ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-beds'],
            [
                'name_dr' => 'ایجاد بسترها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-beds'],
            [
                'name_dr' => 'تصحیح بسترها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-beds'],
            [
                'name_dr' => 'حذف بسترها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-lab-types'],
            [
                'name_dr' => 'ایجاد نوعیت معاینات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-lab-types'],
            [
                'name_dr' => 'تصحیح نوعیت معاینات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-lab-types'],
            [
                'name_dr' => 'حذف نوعیت معاینات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-labs'],
            [
                'name_dr' => 'ایجاد تست ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-labs'],
            [
                'name_dr' => 'تصحیح تست ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-labs'],
            [
                'name_dr' => 'حذف تست ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-procedure-types'],
            [
                'name_dr' => 'ایجاد نوعیت پروسیجر های ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-procedure-types'],
            [
                'name_dr' => 'تصحیح نوعیت پروسیجر های ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-procedure-types'],
            [
                'name_dr' => 'حذف نوعیت پروسیجر های ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-operation-types'],
            [
                'name_dr' => 'ایجاد نوعیت عملیات ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-operation-types'],
            [
                'name_dr' => 'تصحیح نوعیت عملیات ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-operation-types'],
            [
                'name_dr' => 'حذف نوعیت عملیات ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-medicine-types'],
            [
                'name_dr' => 'ایجاد نوعیت ادویه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-medicine-types'],
            [
                'name_dr' => 'تصحیح نوعیت ادویه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-medicine-types'],
            [
                'name_dr' => 'حذف نوعیت ادویه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-medicines'],
            [
                'name_dr' => 'ایجاد ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-medicines'],
            [
                'name_dr' => 'تصحیح ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-medicines'],
            [
                'name_dr' => 'حذف ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-medicines-usage-types'],
            [
                'name_dr' => 'ایجاد طروق تطبیق ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-medicines-usage-types'],
            [
                'name_dr' => 'تصحیح طروق تطبیق ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-medicines-usage-types'],
            [
                'name_dr' => 'حذف طروق تطبیق ادویه جات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-hospitalization-foods'],
            [
                'name_dr' => 'ایجاد نوعیت غذا های بستر',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-hospitalization-foods'],
            [
                'name_dr' => 'تصحیح نوعیت غذا های بستر',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-hospitalization-foods'],
            [
                'name_dr' => 'حذف نوعیت غذا های بستر',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-diseases'],
            [
                'name_dr' => 'ایجاد امراض',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-diseases'],
            [
                'name_dr' => 'تصحیح امراض',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-diseases'],
            [
                'name_dr' => 'حذف امراض',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-branches'],
            [
                'name_dr' => 'ایجاد شفاخانه ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-branches'],
            [
                'name_dr' => 'تصحیح شفاخانه ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-branches'],
            [
                'name_dr' => 'حذف شفاخانه ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-recipients'],
            [
                'name_dr' => 'ایجاد ادارات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-recipients'],
            [
                'name_dr' => 'تصحیح ادارات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-recipients-menu'],
            [
                'name_dr' => 'تصحیح ادارات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-recipient-parts-menu'],
            [
                'name_dr' => 'نمایش مینو جزوات مربوطه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-recipient-parts'],
            [
                'name_dr' => 'ایجاد جزوات مربوطه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-recipient-parts'],
            [
                'name_dr' => 'تصحیح جزوات مربوطه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-recipient-parts'],
            [
                'name_dr' => 'حذف جزوات مربوطه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Categories Permissions
        Permission::updateOrCreate(
            ['name' => 'show-categories-menu'],
            [
                'name_dr' => 'نمایش مینو کتګورۍ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'view-categories'],
            [
                'name_dr' => 'نمایش کتګورۍ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-categories'],
            [
                'name_dr' => 'ایجاد کتګورۍ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-categories'],
            [
                'name_dr' => 'تصحیح کتګورۍ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-categories'],
            [
                'name_dr' => 'حذف کتګورۍ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'view-patients'],
            [
                'name_dr' => 'مشاهده مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-patients'],
            [
                'name_dr' => 'ایجاد مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-patients'],
            [
                'name_dr' => 'تصحیح مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-patients'],
            [
                'name_dr' => 'حذف مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore-patients'],
            [
                'name_dr' => 'بازیابی مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'force-delete-patients'],
            [
                'name_dr' => 'حذف دائمی مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'print-patient-card'],
            [
                'name_dr' => 'چاپ کارت مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-appointment'],
            [
                'name_dr' => 'ایجاد ملاقات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'upload-patient-image'],
            [
                'name_dr' => 'آپلود نمود عکس مریض',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'update-appointment-status'],
            [
                'name_dr' => 'تصحیح حالت ملاقات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-appointments'],
            [
                'name_dr' => 'ویرایش ملاقات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-appointments'],
            [
                'name_dr' => 'حذف ملاقات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore-appointments'],
            [
                'name_dr' => 'بازیابی ملاقات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'add-diagnose'],
            [
                'name_dr' => 'اضافه نمودن تشخیص',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'add-prescription'],
            [
                'name_dr' => 'اضافه نمودن نسخه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'show-blood-request-menu'],
            [
                'name_dr' => 'نمایش درخواست خون',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'add-blood-request'],
            [
                'name_dr' => 'ثبت درخواست خون',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-blood-request'],
            [
                'name_dr' => 'حذف درخواست خون',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'add-advice'],
            [
                'name_dr' => 'اضافه نمودن توصیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'add-patient-labs'],
            [
                'name_dr' => 'اضافه نمودن معاینات به مریض',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'add-consultations'],
            [
                'name_dr' => 'اضافه نمودن مشوره ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'refer-to-another-doctor'],
            [
                'name_dr' => 'معرفی مریض به داکتر/دیپارتمنت دیگر',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'patient-under-review'],
            [
                'name_dr' => 'معرفی مریض به تحت مشاهده',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'patient-hospitalization'],
            [
                'name_dr' => 'بستر نمودن مریض',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-hospitalization-rooms'],
            [
                'name_dr' => 'مدیریت اطاق های بستر',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'refer-to-anesthesia'],
            [
                'name_dr' => 'معرفی مریض به اناستیزی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'refer-to-icu'],
            [
                'name_dr' => 'معرفی مریض به ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-diagnoses'],
            [
                'name_dr' => 'تصحیح تشخیص ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-diagnoses'],
            [
                'name_dr' => 'حذف تشخیص ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-advices'],
            [
                'name_dr' => 'تصحیح توصیه ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-advices'],
            [
                'name_dr' => 'حذف توصیه ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-consultations'],
            [
                'name_dr' => 'تصحیح مشوره ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-consultations'],
            [
                'name_dr' => 'حذف مشوره ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-under-reviews'],
            [
                'name_dr' => 'تصحیح تحت مشاهده',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-under-reviews'],
            [
                'name_dr' => 'حذف تحت مشاهده',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-hospitalizations'],
            [
                'name_dr' => 'تصحیح بستر مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-hospitalizations'],
            [
                'name_dr' => 'حذف بستر مریضان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-anesthesias'],
            [
                'name_dr' => 'تصحیح انستیزی ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-anesthesias'],
            [
                'name_dr' => 'حذف انستیزی ها',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-icus'],
            [
                'name_dr' => 'تصحیح ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-icus'],
            [
                'name_dr' => 'حذف ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-prescription'],
            [
                'name_dr' => 'تصحیح نمودن نسخه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-prescription'],
            [
                'name_dr' => 'حذف نمودن نسخه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-lab-items'],
            [
                'name_dr' => 'تصحیح نمودن معاینات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-lab-items'],
            [
                'name_dr' => 'حذف نمودن معاینات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-under-review-visit'],
            [
                'name_dr' => 'تصحیح نمودن بازدید تحت مشاهده',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-under-review-visit'],
            [
                'name_dr' => 'حذف نمودن بازدید تحت مشاهده',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-daily-icu-progress'],
            [
                'name_dr' => 'تصحیح پیشرفت روزمره ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-icu-procedure'],
            [
                'name_dr' => 'تصحیح پروسیجر ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-icu-procedure'],
            [
                'name_dr' => 'حذف پروسیجر ICU',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        //Militery Types        
        Permission::updateOrCreate(
            ['name' => 'show-militery-types'],
            [
                'name_dr' => 'نمایش رتبه نظامی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-militery-types'],
            [
                'name_dr' => 'ایجاد رتبه نظامی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-militery-types'],
            [
                'name_dr' => 'تصحیح رتبه نظامی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-militery-types'],
            [
                'name_dr' => 'حذف رتبه نظامی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Nurse Permissions
        Permission::updateOrCreate(
            ['name' => 'show-nurses-menu'],
            [
                'name_dr' => 'نمایش مینو پرستاران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'view-nurses'],
            [
                'name_dr' => 'نمایش پرستاران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-nurses'],
            [
                'name_dr' => 'ایجاد پرستاران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-nurses'],
            [
                'name_dr' => 'تصحیح پرستاران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-nurses'],
            [
                'name_dr' => 'حذف پرستاران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore-nurses'],
            [
                'name_dr' => 'بازیابی پرستاران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'force-delete-nurses'],
            [
                'name_dr' => 'حذف دائمی پرستاران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Doctor Permissions
        Permission::updateOrCreate(
            ['name' => 'show-doctors-menu'],
            [
                'name_dr' => 'نمایش مینو داکتران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'view-doctors'],
            [
                'name_dr' => 'نمایش داکتران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-doctors'],
            [
                'name_dr' => 'ایجاد داکتران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-doctors'],
            [
                'name_dr' => 'تصحیح داکتران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-doctors'],
            [
                'name_dr' => 'حذف داکتران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore-doctors'],
            [
                'name_dr' => 'بازیابی داکتران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'force-delete-doctors'],
            [
                'name_dr' => 'حذف دائمی داکتران',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Diabetes Chart Permissions
        Permission::updateOrCreate(
            ['name' => 'show-diabetes-charts-menu'],
            [
                'name_dr' => 'نمایش مینو چارت دیابت',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'view-diabetes-charts'],
            [
                'name_dr' => 'نمایش چارت دیابت',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-diabetes-charts'],
            [
                'name_dr' => 'ایجاد چارت دیابت',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-diabetes-charts'],
            [
                'name_dr' => 'تصحیح چارت دیابت',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-diabetes-charts'],
            [
                'name_dr' => 'حذف چارت دیابت',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore-diabetes-charts'],
            [
                'name_dr' => 'بازیابی چارت دیابت',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'force-delete-diabetes-charts'],
            [
                'name_dr' => 'حذف دائمی چارت دیابت',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Nurse Notes Permissions
        Permission::updateOrCreate(
            ['name' => 'show-nurse-notes-menu'],
            [
                'name_dr' => 'نمایش مینو یادداشت پرستار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'view-nurse-notes'],
            [
                'name_dr' => 'نمایش یادداشت پرستار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create-nurse-notes'],
            [
                'name_dr' => 'ایجاد یادداشت پرستار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit-nurse-notes'],
            [
                'name_dr' => 'تصحیح یادداشت پرستار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete-nurse-notes'],
            [
                'name_dr' => 'حذف یادداشت پرستار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore-nurse-notes'],
            [
                'name_dr' => 'بازیابی یادداشت پرستار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'force-delete-nurse-notes'],
            [
                'name_dr' => 'حذف دائمی یادداشت پرستار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Nutrition Care Permissions
        Permission::updateOrCreate(
            ['name' => 'show-nutrition-care-menu'],
            [
                'name_dr' => 'نمایش مینو مراقبت تغذیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'view_nutrition_care'],
            [
                'name_dr' => 'نمایش مراقبت تغذیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create_nutrition_care'],
            [
                'name_dr' => 'ایجاد مراقبت تغذیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit_nutrition_care'],
            [
                'name_dr' => 'تصحیح مراقبت تغذیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete_nutrition_care'],
            [
                'name_dr' => 'حذف مراقبت تغذیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore_nutrition_care'],
            [
                'name_dr' => 'بازیابی مراقبت تغذیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'force_delete_nutrition_care'],
            [
                'name_dr' => 'حذف دائمی مراقبت تغذیه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Nursing Assessment Menu Permission
        Permission::updateOrCreate(
            ['name' => 'show-nursing-assessments-menu'],
            [
                'name_dr' => 'نمایش مینو ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Nursing Assessment CRUD Permissions
        Permission::updateOrCreate(
            ['name' => 'view_nursing_assessment'],
            [
                'name_dr' => 'نمایش ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'create_nursing_assessment'],
            [
                'name_dr' => 'ایجاد ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'edit_nursing_assessment'],
            [
                'name_dr' => 'تصحیح ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'delete_nursing_assessment'],
            [
                'name_dr' => 'حذف ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'restore_nursing_assessment'],
            [
                'name_dr' => 'بازیابی ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'force_delete_nursing_assessment'],
            [
                'name_dr' => 'حذف دائمی ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Print permission
        Permission::updateOrCreate(
            ['name' => 'print_nursing_assessment'],
            [
                'name_dr' => 'چاپ ارزیابی نرسنگ',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Laboratory Test Management System permissions
        Permission::updateOrCreate(
            ['name' => 'show-laboratory-menu'],
            [
                'name_dr' => 'نمایش مینو آزمایشگاه',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-test-categories'],
            [
                'name_dr' => 'مدیریت دسته بندی آزمایشات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-lab-tests'],
            [
                'name_dr' => 'مدیریت آزمایشات',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-test-parameters'],
            [
                'name_dr' => 'مدیریت پارامترهای آزمایش',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'register-patient-tests'],
            [
                'name_dr' => 'ثبت آزمایشات بیمار',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'manage-test-results'],
            [
                'name_dr' => 'مدیریت نتایج آزمایش',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'print-lab-reports'],
            [
                'name_dr' => 'چاپ گزارشات آزمایش',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        // Dentist Registrations Permission
        Permission::updateOrCreate(
            ['name' => 'access-dentist-registrations'],
            [
                'name_dr' => 'دسترسی به بخش دندان',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'access-nephrology-registrations'],
            [
                'name_dr' => 'دسترسی به بخش نفرولوژی',
                'name_pa' => NULL,
                'guard_name' => 'web',
            ]
        );

        $this->call(PhysiotherapyPermissionSeeder::class);
        $this->call(PharmacyPermissionSeeder::class);
        $this->call(NursingAssessmentPermissionSeeder::class);
        $this->call(NursingAssessmentRolePermissionSeeder::class);
        $this->call(PhysiotherapyTypeSeeder::class);
        // $this->call(PhysiotherapyProcedureReviewSeeder::class);
        $this->call(VitalSignPermissionSeeder::class);
        $this->call(VitalSignRolePermissionSeeder::class);

        $bloodBankRole = Role::where('name', 'blood-bank')->first();
        if ($bloodBankRole) {
            $bloodBankRole->givePermissionTo([
                'show-blood-bank-menu',
                'manage-blood-inventory',
                'issue-blood',
                'receive-blood-units',
            ]);
        }

        foreach (['admin', 'super_admin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo('manage-hospitalization-rooms');
            }
        }

        User::find(1)->givePermissionTo(Permission::all());

    }
}
