<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PrescriptionStock;
use App\Models\Income;
use App\Models\Outcome;
use App\Models\Medicine;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Models\Patient;

class PrescriptionStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing medicines, users, and patients for sample data
        $medicines = Medicine::take(5)->get();
        $users = User::take(3)->get();
        $patients = Patient::take(3)->get();
        $prescriptionItems = PrescriptionItem::take(3)->get();

        if ($medicines->isEmpty()) {
            $this->command->info('Skipping PrescriptionStockSeeder: No medicines found');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->info('Skipping PrescriptionStockSeeder: No users found');
            return;
        }

        foreach ($medicines as $medicine) {
            // Note: PrescriptionStock is now a view that calculates from income and outcome
            // No need to create stock records manually

            // Create sample income records
            for ($i = 0; $i < rand(2, 5); $i++) {
                Income::create([
                    'medicine_id' => $medicine->id,
                    'amount' => rand(20, 100),
                    'batch_number' => 'BATCH-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'expiry_date' => now()->addMonths(rand(6, 24)),
                    'supplier_name' => 'Supplier ' . rand(1, 5),
                    'supplier_contact' => '+1234567890',
                    'purchase_price' => rand(5, 50) + (rand(0, 99) / 100),
                    'purchase_date' => now()->subDays(rand(1, 90)),
                    'invoice_number' => 'INV-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'income_type' => ['purchase', 'donation', 'return'][rand(0, 2)],
                    'notes' => 'Sample income entry',
                    'created_by' => $users->first()->id,
                ]);
            }

            // Create sample outcome records
            for ($i = 0; $i < rand(1, 3); $i++) {
                $outcomeData = [
                    'medicine_id' => $medicine->id,
                    'amount' => rand(5, 30),
                    'outcome_type' => ['prescription', 'expired', 'damaged'][rand(0, 2)],
                    'batch_number' => 'BATCH-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'outcome_date' => now()->subDays(rand(1, 30)),
                    'notes' => 'Sample outcome entry',
                    'created_by' => $users->first()->id,
                ];

                // Add prescription-related data if outcome type is prescription
                if ($outcomeData['outcome_type'] === 'prescription' && !$prescriptionItems->isEmpty()) {
                    $outcomeData['prescription_item_id'] = $prescriptionItems->random()->id;
                    $outcomeData['patient_id'] = $patients->random()->id ?? null;
                    $outcomeData['doctor_id'] = $users->random()->id;
                    $outcomeData['reason'] = 'Prescribed to patient';
                } else {
                    $outcomeData['reason'] = 'Stock adjustment';
                }

                Outcome::create($outcomeData);
            }
        }

        $this->command->info('PrescriptionStockSeeder completed successfully!');
    }
}
