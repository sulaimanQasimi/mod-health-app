<?php

namespace Database\Seeders;

use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyProcedureReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class PhysiotherapyProcedureReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing physiotherapy procedures and users
        $procedures = PhysiotherapyProcedure::all();
        $users = User::all();

        if ($procedures->isEmpty() || $users->isEmpty()) {
            return;
        }

        // Create sample reviews for each procedure
        $procedures->each(function ($procedure) use ($users) {
            // Create 1-3 reviews per procedure
            $reviewCount = rand(1, 3);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                PhysiotherapyProcedureReview::create([
                    'physiotherapy_procedure_id' => $procedure->id,
                    'description' => $this->getSampleDescription(),
                    'status' => $this->getRandomStatus(),
                    'created_by' => $users->random()->id,
                    'updated_by' => $users->random()->id,
                ]);
            }
        });
    }

    /**
     * Get a sample description for the review.
     */
    private function getSampleDescription(): string
    {
        $descriptions = [
            'Patient showed good progress in mobility exercises.',
            'Range of motion has improved significantly.',
            'Patient needs more time to complete the treatment plan.',
            'Therapeutic exercises are well tolerated by the patient.',
            'Patient requires additional sessions for full recovery.',
            'Treatment plan is effective and should continue.',
            'Patient demonstrates good understanding of home exercises.',
            'Progress is slower than expected, needs adjustment.',
            'Patient is responding well to the current treatment.',
            'Additional modalities may be beneficial for this case.'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Get a random status for the review.
     */
    private function getRandomStatus(): string
    {
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        return $statuses[array_rand($statuses)];
    }
}
