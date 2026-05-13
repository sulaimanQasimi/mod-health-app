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
        $labTypes = [ ];

        foreach($labTypes as $labType){

            LabType::create($labType);
        }
    }
}
