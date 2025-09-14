<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DiabetesChart;
use App\Models\Nurse;
use App\Models\Medicine;
use App\Models\UnderReview;
use App\Models\Hospitalization;
use App\Models\User;

class DiabetesChartSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get some sample data
        $nurses = Nurse::take(3)->get();
        $medicines = Medicine::take(5)->get();
        $underReviews = UnderReview::take(3)->get();
        $hospitalizations = Hospitalization::take(3)->get();
        $adminUser = User::first();

        if ($nurses->isEmpty() || $medicines->isEmpty() || ($underReviews->isEmpty() && $hospitalizations->isEmpty())) {
            $this->command->warn('Not enough sample data to create diabetes charts. Please ensure you have nurses, medicines, and at least one under_review or hospitalization record.');
            return;
        }

        // Disable model events to prevent issues with created_by
        DiabetesChart::unsetEventDispatcher();

        $sampleData = [
            // Under Review Records
            [
                'nurse_id' => $nurses->random()->id,
                'medicine_id' => $medicines->random()->id,
                'insulin_dose' => 10.5,
                'rbs' => 180.5,
                'fbs' => null,
                'unit' => 'mg/dl',
                'time' => '08:30:00',
                'date' => now()->subDays(1)->format('Y-m-d'),
                'diabetes_chartable_type' => 'App\\Models\\UnderReview',
                'diabetes_chartable_id' => $underReviews->first()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nurse_id' => $nurses->random()->id,
                'medicine_id' => $medicines->random()->id,
                'insulin_dose' => 8.0,
                'rbs' => null,
                'fbs' => 95.0,
                'unit' => 'mg/dl',
                'time' => '07:00:00',
                'date' => now()->subDays(2)->format('Y-m-d'),
                'diabetes_chartable_type' => 'App\\Models\\UnderReview',
                'diabetes_chartable_id' => $underReviews->skip(1)->first()->id ?? $underReviews->first()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Hospitalization Records
            [
                'nurse_id' => $nurses->random()->id,
                'medicine_id' => $medicines->random()->id,
                'insulin_dose' => 12.0,
                'rbs' => 220.0,
                'fbs' => null,
                'unit' => 'mg/dl',
                'time' => '14:15:00',
                'date' => now()->subDays(3)->format('Y-m-d'),
                'diabetes_chartable_type' => 'App\\Models\\Hospitalization',
                'diabetes_chartable_id' => $hospitalizations->first()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nurse_id' => $nurses->random()->id,
                'medicine_id' => $medicines->random()->id,
                'insulin_dose' => 6.5,
                'rbs' => null,
                'fbs' => 88.0,
                'unit' => 'mg/dl',
                'time' => '06:45:00',
                'date' => now()->subDays(4)->format('Y-m-d'),
                'diabetes_chartable_type' => 'App\\Models\\Hospitalization',
                'diabetes_chartable_id' => $hospitalizations->skip(1)->first()->id ?? $hospitalizations->first()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mixed records
            [
                'nurse_id' => $nurses->random()->id,
                'medicine_id' => $medicines->random()->id,
                'insulin_dose' => 15.0,
                'rbs' => 195.5,
                'fbs' => null,
                'unit' => 'mg/dl',
                'time' => '20:30:00',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'diabetes_chartable_type' => 'App\\Models\\UnderReview',
                'diabetes_chartable_id' => $underReviews->last()->id ?? $underReviews->first()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nurse_id' => $nurses->random()->id,
                'medicine_id' => $medicines->random()->id,
                'insulin_dose' => null,
                'rbs' => 165.0,
                'fbs' => null,
                'unit' => 'mg/dl',
                'time' => '12:00:00',
                'date' => now()->subDays(6)->format('Y-m-d'),
                'diabetes_chartable_type' => 'App\\Models\\Hospitalization',
                'diabetes_chartable_id' => $hospitalizations->last()->id ?? $hospitalizations->first()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sampleData as $data) {
            // Only create if the chartable record exists
            if ($data['diabetes_chartable_type'] === 'App\\Models\\UnderReview') {
                $chartable = UnderReview::find($data['diabetes_chartable_id']);
            } else {
                $chartable = Hospitalization::find($data['diabetes_chartable_id']);
            }

            if ($chartable) {
                DiabetesChart::create($data);
            }
        }

        // Re-enable model events
        DiabetesChart::setEventDispatcher(app('events'));

        $this->command->info('Diabetes chart sample data created successfully!');
    }
}