<?php

namespace Database\Seeders;

use App\Models\Floor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $floors = [
            ['id' => '1','name' => 'اول منزل','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'دوهم منزل','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'دریم منزل','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($floors as $floor){

            Floor::create($floor);
        }
    }
}
