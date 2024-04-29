<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['id' => '1','name' => 'Emergency Department','department_id' => '1','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'ICU','department_id' => '1','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'Cardiology','department_id' => '2','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '4','name' => 'Pediatrics','department_id' => '3','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '5','name' => 'Obstetrics','department_id' => '1','branch_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($sections as $section){

            Section::create($section);
        }
    }
}
