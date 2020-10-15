<?php

use Illuminate\Database\Seeder;
use App\Models\Entity;
use Illuminate\Support\Facades\DB;

class EntitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [

            [
                'id'  => 1,
                'name' => 'Доступы к страницам',
            ],
            [
                'id'  => 2,
                'name' => 'Роль для',
            ],
            [
                'id'  => 3,
                'name' => 'Доступ к организациям, проектам и ГЕО',
            ],
            [
                'id'  => 4,
                'name' => 'Доступ к статусам',
            ]
        ];

        foreach ($data as $item) {
            try {

                $entity = Entity::create($item);

            } catch (Exception $e) {
                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
            }
        }

        $max    = DB::table('entities')->max('id') + 1;
        DB::statement('ALTER SEQUENCE entities_id_seq RESTART WITH ' . $max . ';');
    }
}
