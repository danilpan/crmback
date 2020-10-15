<?php

use App\Repositories\Optovichok\ClientTypeRepository;
use Illuminate\Database\Seeder;

class ClientTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo = resolve(ClientTypeRepository::class);

        $data = [
            [
                'id' => '1',
                'title' => 'Не резидент'
            ],
            [
                'id' => '2',
                'title' => 'Покупатель'
            ],
            [
                'id' => '3',
                'title' => 'Поставщик'
            ]
        ];

        foreach ($data as $item) {
            $repo->create($item);
        }

    }
}