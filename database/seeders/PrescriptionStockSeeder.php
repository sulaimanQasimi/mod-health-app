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
        $this->command->info('PrescriptionStockSeeder completed successfully!');
    }
}
