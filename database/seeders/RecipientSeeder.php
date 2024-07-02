<?php

namespace Database\Seeders;

use App\Models\Recipient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipients = [
            ['id' => '1','name' => 'وزارت دفاع ملی','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '2','name' => 'وزارت امور داخله','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '3','name' => 'وزارت مالیه','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '4','name' => 'وزارت سرحدات','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
            ['id' => '5','name' => 'وزارت معارف','created_at' => '2020-06-20 06:44:57','updated_at' => '2020-06-20 06:44:57'],
        ];

        foreach($recipients as $recipient){

            Recipient::create($recipient);
        }
    }
}
