<?php

namespace Database\Seeders;

use App\Models\Relation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $relations = [
            ['id' => '1','name' => 'پلار'],
            ['id' => '2','name' => 'مور'],
            ['id' => '3','name' => 'زوی'],
            ['id' => '4','name' => 'لور'],
            ['id' => '5','name' => 'ښځه'],
        ];

        foreach($relations as $relation){

            Relation::create($relation);
        }
    }
}
