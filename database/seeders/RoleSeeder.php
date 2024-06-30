<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create( [
            'id'=>1,
            'name'=>'super_admin',
            'name_dr'=>'ادمین عمومی',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:13:33',
            'updated_at'=>'2023-08-22 10:13:33'
            ] );

            Role::create( [
            'id'=>2,
            'name'=>'anesthesia_approve',
            'name_dr'=>'کاربر انستیزی',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:13:33',
            'updated_at'=>'2023-08-22 10:13:33'
            ] );

            Role::create( [
            'id'=>3,
            'name'=>'hospitalization_visits',
            'name_dr'=>'کاربر بازدید های بستر',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:13:57',
            'updated_at'=>'2023-08-22 10:13:57'
            ] );

            Role::create( [
            'id'=>4,
            'name'=>'icu_visits',
            'name_dr'=>'کاربر ICU',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:14:09',
            'updated_at'=>'2023-08-22 10:14:09'
            ] );

            Role::create( [
            'id'=>5,
            'name'=>'lab_checkups',
            'name_dr'=>'کاربر معاینات',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:14:20',
            'updated_at'=>'2023-08-22 10:14:20'
            ] );

            Role::create( [
            'id'=>6,
            'name'=>'operations_approve',
            'name_dr'=>'کاربر عملیات',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:14:39',
            'updated_at'=>'2023-08-22 10:14:39'
            ] );

            Role::create( [
            'id'=>7,
            'name'=>'prescription_issue',
            'name_dr'=>'کاربر توزیع نسخه جات',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:14:48',
            'updated_at'=>'2023-08-22 10:14:48'
            ] );

            Role::create( [
            'id'=>8,
            'name'=>'opd-doctor',
            'name_dr'=>'داکتر OPD',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:14:48',
            'updated_at'=>'2023-08-22 10:14:48'
            ] );

            Role::create( [
            'id'=>9,
            'name'=>'blood-bank',
            'name_dr'=>'کارمند بانک خون',
            'name_pa'=>NULL,
            'guard_name'=>'web',
            'recipients'=>NULL,
            'sector_id'=>NULL,
            'created_at'=>'2023-08-22 10:14:48',
            'updated_at'=>'2023-08-22 10:14:48'
            ] );

            Role::create( [
                'id'=>10,
                'name'=>'nurse',
                'name_dr'=>'نرس',
                'name_pa'=>NULL,
                'guard_name'=>'web',
                'recipients'=>NULL,
                'sector_id'=>NULL,
                'created_at'=>'2023-08-22 10:14:09',
                'updated_at'=>'2023-08-22 10:14:09'
                ] );

    }
}
