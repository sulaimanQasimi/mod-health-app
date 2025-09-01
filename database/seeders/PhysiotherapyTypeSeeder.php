<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhysiotherapyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('physiotherapy_types')->insert([
            [
                'name' => 'Physical Therapy',
                'description' => 'General physical therapy for rehabilitation and recovery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Occupational Therapy',
                'description' => 'Therapy focused on daily living activities and functional skills',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sports Rehabilitation',
                'description' => 'Specialized therapy for sports-related injuries and recovery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Neurological Rehabilitation',
                'description' => 'Therapy for patients with neurological conditions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cardiopulmonary Rehabilitation',
                'description' => 'Therapy for heart and lung conditions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pediatric Physiotherapy',
                'description' => 'Specialized therapy for children and infants',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Geriatric Physiotherapy',
                'description' => 'Therapy focused on elderly patients and age-related conditions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Orthopedic Rehabilitation',
                'description' => 'Therapy for musculoskeletal injuries and conditions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
