<?php

use Illuminate\Database\Seeder;

class AtsStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ats_statuses')->truncate();
        DB::table('ats_statuses')->insert([
            'id'      => 1,
            'name_en' => "Offline",
            'name_ru' => "Оффлайн",
            'comment' => "",
        ]);
        
        DB::table('ats_statuses')->insert([
            'id'      => 2,
            'name_en' => "Online",
            'name_ru' => "Онлайн",
            'comment' => "",
        ]);
        
        DB::table('ats_statuses')->insert([
            'id'      => 3,
            'name_en' => "Speak",
            'name_ru' => "Звонок",
            'comment' => "",
        ]);

        DB::table('ats_statuses')->insert([
            'id'      => 4,
            'name_en' => "Ringing",
            'name_ru' => "Не берет",
            'comment' => "",
        ]);
    }
}
