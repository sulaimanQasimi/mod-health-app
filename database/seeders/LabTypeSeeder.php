<?php

namespace Database\Seeders;

use App\Models\LabType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labTypes = [
            ['id' => '1','name' => 'Blood Test','branch_id' =>'1','parent_id' =>'1','section_id' =>'1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'COVID19 Test','branch_id' =>'1','parent_id' =>'1','section_id' =>'1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'Culture Test','branch_id' =>'1','parent_id' =>'1','section_id' =>'1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '4','name' => 'HIV Test','branch_id' =>'1','parent_id' =>'1','section_id' =>'1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '5','name' => 'HP Test','branch_id' =>'1','parent_id' =>'1','section_id' =>'1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($labTypes as $labType){

            LabType::create($labType);
        }
    }
}
