<?php

namespace Database\Seeders;

use App\Models\LabTypeSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabTypeSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labTypeSections = [
            ['id' => '1','section' => 'معاینات','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','section' => 'لابراتوار','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','section' => 'رادیولوژی','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($labTypeSections as $labTypeSection){

            LabTypeSection::create($labTypeSection);
        }
    }
}
