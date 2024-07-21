<?php

namespace Database\Seeders;

use App\Models\MedicineUsageType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $medicineUsageTypes = [
        ['id' => '1', 'name' => 'Oral'],
        ['id' => '2', 'name' => 'Topical'],
        ['id' => '3', 'name' => 'Parenteral'],
        ['id' => '4', 'name' => 'Inhalation'],
        ['id' => '5', 'name' => 'Sublingual'],
        ['id' => '6', 'name' => 'Rectal'],
        ['id' => '7', 'name' => 'Transdermal'],
        ['id' => '8', 'name' => 'Ophthalmic'],
        ['id' => '9', 'name' => 'Nasal'],
        ['id' => '10', 'name' => 'Vaginal'],
    ];

    foreach ($medicineUsageTypes as $type) {
        MedicineUsageType::create($type);
    }
}
}
