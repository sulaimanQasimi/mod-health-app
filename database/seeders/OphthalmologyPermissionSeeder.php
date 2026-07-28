<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class OphthalmologyPermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'access-ophthalmology-registrations' => ['دسترسی به بخش چشم', 'د سترګو څانګې ته لاسرسی'],
        'create-ophthalmology-registrations' => ['ایجاد ثبت چشم', 'د سترګو ثبت جوړول'],
        'edit-ophthalmology-registrations' => ['ویرایش معاینه چشم', 'د سترګو معاینه سمول'],
        'delete-ophthalmology-registrations' => ['حذف ثبت چشم', 'د سترګو ثبت ړنګول'],
        'change-ophthalmology-status' => ['تغییر وضعیت ثبت چشم', 'د سترګو ثبت حالت بدلول'],
        'upload-ophthalmology-images' => ['آپلود تصاویر چشم', 'د سترګو انځورونه پورته کول'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', 'manage-ophthalmology-tests')->delete();

        foreach (self::PERMISSIONS as $name => [$nameDr, $namePa]) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name_dr' => $nameDr, 'name_pa' => $namePa],
            );
        }

        $all = array_keys(self::PERMISSIONS);
        $clinical = array_values(array_diff($all, ['delete-ophthalmology-registrations']));

        foreach (['admin', 'super_admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($all);
        }

        Role::where('name', 'opd-doctor')->first()?->givePermissionTo($clinical);

        $ophthalmologist = Role::firstOrCreate(
            ['name' => 'ophthalmologist', 'guard_name' => 'web'],
            ['name_dr' => 'داکتر چشم', 'name_pa' => 'د سترګو ډاکټر'],
        );
        $ophthalmologist->givePermissionTo($clinical);

        $technician = Role::firstOrCreate(
            ['name' => 'ophthalmology-technician', 'guard_name' => 'web'],
            ['name_dr' => 'تخنیکر چشم', 'name_pa' => 'د سترګو تخنیکر'],
        );
        $technician->givePermissionTo([
            'access-ophthalmology-registrations',
            'upload-ophthalmology-images',
        ]);

        $receptionist = Role::firstOrCreate(
            ['name' => 'ophthalmology-receptionist', 'guard_name' => 'web'],
            ['name_dr' => 'پذیرش چشم', 'name_pa' => 'د سترګو پذیرش'],
        );
        $receptionist->givePermissionTo([
            'access-ophthalmology-registrations',
            'create-ophthalmology-registrations',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
