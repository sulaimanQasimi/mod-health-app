<?php

namespace Database\Seeders;

use App\Models\Disease;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiseaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diseases = [
            [
                'id' => 1,
                'name' => 'Diabetes Mellitus',
                'description' => 'A group of diseases that affect how your body uses blood sugar (glucose).',
                'deleted_at' => null,
                'created_at' => '2020-06-20 06:44:57',
                'updated_at' => '2020-06-20 06:44:57',
            ],
            [
                'id' => 2,
                'name' => 'Hypertension',
                'description' => 'A condition in which the force of the blood against the artery walls is too high.',
                'deleted_at' => null,
                'created_at' => '2020-06-21 07:00:00',
                'updated_at' => '2020-06-21 07:00:00',
            ],
            [
                'id' => 3,
                'name' => 'Asthma',
                'description' => 'A condition in which your airways narrow and swell, producing extra mucus.',
                'deleted_at' => null,
                'created_at' => '2020-06-22 08:15:00',
                'updated_at' => '2020-06-22 08:15:00',
            ],
            [
                'id' => 4,
                'name' => 'Chronic Obstructive Pulmonary Disease (COPD)',
                'description' => 'A group of lung diseases that block airflow and make it difficult to breathe.',
                'deleted_at' => null,
                'created_at' => '2020-06-23 09:30:00',
                'updated_at' => '2020-06-23 09:30:00',
            ],
            [
                'id' => 5,
                'name' => 'Coronary Artery Disease',
                'description' => 'A condition where the coronary arteries become narrowed or blocked.',
                'deleted_at' => null,
                'created_at' => '2020-06-24 10:45:00',
                'updated_at' => '2020-06-24 10:45:00',
            ],
            [
                'id' => 6,
                'name' => 'Osteoarthritis',
                'description' => 'A type of arthritis that occurs when flexible tissue at the ends of bones wears down.',
                'deleted_at' => null,
                'created_at' => '2020-06-25 11:00:00',
                'updated_at' => '2020-06-25 11:00:00',
            ],
            [
                'id' => 7,
                'name' => 'Alzheimer\'s Disease',
                'description' => 'A progressive disease that destroys memory and other important mental functions.',
                'deleted_at' => null,
                'created_at' => '2020-06-26 12:15:00',
                'updated_at' => '2020-06-26 12:15:00',
            ],
            [
                'id' => 8,
                'name' => 'Parkinson’s Disease',
                'description' => 'A progressive nervous system disorder that affects movement.',
                'deleted_at' => null,
                'created_at' => '2020-06-27 13:30:00',
                'updated_at' => '2020-06-27 13:30:00',
            ],
            [
                'id' => 9,
                'name' => 'Rheumatoid Arthritis',
                'description' => 'An autoimmune disorder that primarily affects joints, causing inflammation and pain.',
                'deleted_at' => null,
                'created_at' => '2020-06-28 14:45:00',
                'updated_at' => '2020-06-28 14:45:00',
            ],
            [
                'id' => 10,
                'name' => 'Multiple Sclerosis',
                'description' => 'A disease in which the immune system eats away at the protective covering of nerves.',
                'deleted_at' => null,
                'created_at' => '2020-06-29 15:00:00',
                'updated_at' => '2020-06-29 15:00:00',
            ],
        ];

        foreach ($diseases as $disease) {
            Disease::create($disease);
        }
    }
}