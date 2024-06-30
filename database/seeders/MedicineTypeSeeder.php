<?php

namespace Database\Seeders;

use App\Models\MedicineType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicineTypes = [
            ['id' => '1','type' => 'Tablet'],
            ['id' => '2','type' => 'Syrup'],
            ['id' => '3','type' => 'Injection'],
            ['id' => '4','type' => 'Capsule'],
            ['id' => '5','type' => 'Drops'],
        ];

        foreach($medicineTypes as $medicineType){

            MedicineType::create($medicineType);
        }
    }
}
