<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MiliteryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('militery_types')->insert([
            'name' => 'جنرال',
        ],
        [
            'name' => 'دگر جنرال',
        ],);
    }
}
