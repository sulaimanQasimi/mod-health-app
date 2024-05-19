<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            ['id' => '1','name' => 'KABUL BRANCH', 'address' => 'Kabul, Wazir Akbar Khan', 'created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'KANDAHAR BRANCH', 'address' => 'Kabul, Wazir Akbar Khan', 'created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'MAZAR BRANCH', 'address' => 'Kabul, Wazir Akbar Khan', 'created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($branches as $branch){

            Branch::create($branch);
        }
    }
}
