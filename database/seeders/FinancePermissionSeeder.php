<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class FinancePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'access-finance' => ['دسترسی به مالی', 'مالي ته لاسرسی'],
        'manage-finance' => ['مدیریت مالی', 'د مالیې مدیریت'],
        'pay-invoices' => ['تادیه بل', 'د فاکتور تادیه'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name => [$nameDr, $namePa]) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name_dr' => $nameDr, 'name_pa' => $namePa],
            );
        }

        $all = array_keys(self::PERMISSIONS);

        foreach (['admin', 'super_admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($all);
        }

        $cashier = Role::firstOrCreate(
            ['name' => 'cashier', 'guard_name' => 'web'],
            ['name_dr' => 'کیشیر', 'name_pa' => 'صندوقدار'],
        );
        $cashier->givePermissionTo($all);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
