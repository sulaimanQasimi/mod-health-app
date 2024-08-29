<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $faker->addProvider(new \Bezhanov\Faker\Provider\Medicine($faker));

        $medicines = [];

        for ($i = 1; $i <= 200; $i++) {
            $medicines[] = [
                'id' => $i,
                'name' => $faker->medicine,
                'medicine_type_id' => $faker->numberBetween(1, 5),
                'disease_id' => '["1"]',
                'created_at' => '2020-06-20 06:44:57',
                'updated_at' => '2020-06-20 06:44:57',
            ];
        }

        foreach ($medicines as $medicine) {
            Medicine::create($medicine);
        }
    }
}
