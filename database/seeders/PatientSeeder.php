<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $patients = [];

        for ($i = 1; $i <= 200; $i++) {
            $patients[] = [
                'id' => $i,
                'name' => $faker->firstName,
                'father_name' => $faker->lastName,
                'last_name' => $faker->lastName,
                'phone' => $faker->phoneNumber,
                'age' => $faker->numberBetween(18, 65),
                'job' => $faker->jobTitle,
                'rank' => $faker->numberBetween(1, 5),
                'nid' => $faker->numerify('####-##-####'),
                'branch_id' => $faker->numberBetween(1, 3),
                'province_id' => 1,
                'district_id' => 110,
                'relation_id' => $faker->numberBetween(1, 3),
                'referred_by' => $faker->numberBetween(1, 3),
                'created_at' => $faker->dateTimeBetween('2023-06-01', '2024-06-30')->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeBetween('2023-06-01', '2024-06-30')->format('Y-m-d H:i:s'),
            ];
        }

        foreach ($patients as $patient) {
            Patient::create($patient);
        }
    }
}
