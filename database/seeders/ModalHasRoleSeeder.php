<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModalHasRoleSeeder extends Seeder
{
    public function run(): void
    {

            DB::table('model_has_roles')->insert([
                'role_id'=>'1',
                'model_type'=>'App\\Models\\User',
                'model_id'=>'1'
            ]);
            DB::table('model_has_roles')->insert([
                'role_id'=>'2',
                'model_type'=>'App\\Models\\User',
                'model_id'=>'1'
            ]);
            DB::table('model_has_roles')->insert([
                'role_id'=>'3',
                'model_type'=>'App\\Models\\User',
                'model_id'=>'1'
            ]);
            DB::table('model_has_roles')->insert([
                'role_id'=>'4',
                'model_type'=>'App\\Models\\User',
                'model_id'=>'1'
            ]);
            DB::table('model_has_roles')->insert([
                'role_id'=>'5',
                'model_type'=>'App\\Models\\User',
                'model_id'=>'1'
            ]);
            DB::table('model_has_roles')->insert([
                'role_id'=>'6',
                'model_type'=>'App\\Models\\User',
                'model_id'=>'1'
            ]);
            DB::table('model_has_roles')->insert([
                'role_id'=>'7',
                'model_type'=>'App\\Models\\User',
                'model_id'=>'1'
            ]);
    }
}
