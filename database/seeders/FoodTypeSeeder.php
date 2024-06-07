<?php

namespace Database\Seeders;

use App\Models\FoodType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FoodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foodTypes = [
            ['id' => '1','name' => 'Coma'],
            ['id' => '2','name' => 'CRF'],
            ['id' => '3','name' => 'Septec and Burn'],
            ['id' => '4','name' => 'Diabet'],
            ['id' => '5','name' => 'Bed sore'],
            ['id' => '6','name' => 'Hypartentioni'],
            ['id' => '7','name' => 'Hepatiet'],
            ['id' => '8','name' => 'Ortopedae'],
            ['id' => '9','name' => 'Corona'],
            ['id' => '10','name' => 'Normal'],
        ];

        foreach($foodTypes as $foodType){

            FoodType::create($foodType);
        }
    }
}
