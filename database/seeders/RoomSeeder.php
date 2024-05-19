<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['id' => '1','name' => 'First Room','branch_id' => '1','floor_id' => '1','department_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'Second Room','branch_id' => '1','floor_id' => '2','department_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'Third Room','branch_id' => '1','floor_id' => '3','department_id' => '1','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($rooms as $room){

            Room::create($room);
        }
    }
}
