<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = [
            ['id' => '1','name' => 'Khan Mohammad','father_name' => 'Khan','last_name' => 'Anwar','phone' => '123456789', 'job' => 'test', 'rank' => '1','nid' => '1400-12-255','branch_id' => '1','province_id' => '1','district_id' => '110','relation_id' => '1','referred_by' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'Jan Mohammad','father_name' => 'Khan','last_name' => 'Anwar','phone' => '123456789', 'job' => 'test', 'rank' => '1','nid' => '1400-243-12','branch_id' => '1','province_id' => '1','district_id' => '110','relation_id' => '1','referred_by' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'Laal Khan','father_name' => 'Khan','last_name' => 'Anwar','phone' => '123456789', 'job' => 'test', 'rank' => '1','nid' => '1400-23-12','branch_id' => '2','province_id' => '1','district_id' => '110','relation_id' => '1','referred_by' => '2','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '4','name' => 'Raz Mohammad','father_name' => 'Khan','last_name' => 'Anwar','phone' => '123456789', 'job' => 'test', 'rank' => '1','nid' => '1400-23-142','branch_id' => '3','province_id' => '1','district_id' => '110','relation_id' => '1','referred_by' => '3','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '5','name' => 'Taj Mohammad','father_name' => 'Khan','last_name' => 'Anwar','phone' => '123456789', 'job' => 'test', 'rank' => '1','nid' => '1400-3323-12','branch_id' => '1','province_id' => '1','district_id' => '110','relation_id' => '1','referred_by' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($patients as $patient){

            Patient::create($patient);
        }
    }
}
