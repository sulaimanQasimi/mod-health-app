<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name_en' => 'Super Admin',
            'email' => 'test@test.com',
            'password' => Hash::make('123456789'),
            'status' =>'1',
            'branch_id' => '1',
            'lang' => 'dr'

        ]);
    }
}
