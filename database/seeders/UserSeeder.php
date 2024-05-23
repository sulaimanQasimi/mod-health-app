<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'last_name' => 'Admin',
            'email' => 'test@test.com',
            'password' => Hash::make('123456789'),
            'status' =>'1',
            'branch_id' => '1',
            'department_id' => '1',
            'section_id' => '1',
            'lang' => 'dr',

        ]);
        $faker = Faker::create();

        for ($i = 1; $i < 50; $i++) {
            DB::table('users')->insert([
                'name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('123456789'),
                'status' => '1',
                'branch_id' => $faker->numberBetween(1, 3),
                'department_id' => $faker->numberBetween(1, 5),
                'section_id' => $faker->numberBetween(1, 5),
                'lang' => 'dr',
            ]);
        }
    }
}
