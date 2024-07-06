<?php

namespace Database\Seeders;

use App\Models\ICUProcedureType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcedureTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => '1','name' => 'Intubation','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'Central Line','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'Tracheostomy','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '4','name' => 'Chest Tube/Drain','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '5','name' => 'LP','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '6','name' => 'Gastrostomy','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($types as $type){

            ICUProcedureType::create($type);
        }
    }
}
