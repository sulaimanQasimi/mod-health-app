<?php

namespace Database\Seeders;

use App\Models\VitalSignType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VitalSignTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vitalSignTypes = [
            'Blood Pressure',
            'Pulse Rate',
            'Temperature',
            'Respiratory Rate',
            'Oxygen Saturation',
            'Blood Glucose',
            'Weight',
            'Height',
            'BMI',
            'Pain Scale',
            'Consciousness Level',
            'Urine Output',
            'Heart Rate',
            'Blood Pressure (Systolic)',
            'Blood Pressure (Diastolic)',
        ];

        foreach ($vitalSignTypes as $type) {
            VitalSignType::firstOrCreate(['name' => $type]);
        }
    }
}
