<?php

use Illuminate\Database\Seeder;
use App\Repositories\CurrencyRepository;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo    = resolve(CurrencyRepository::class);

        $data = [

            [
                'id' => '1',
                'name' => 'руб'
            ],
            [
                'id' => '2',
                'name' => 'тг'
            ],
            [
                'id' => '3',
                'name' => 'грн'
            ],
            [
                'id' => '4',
                'name' => '$'
            ],
            [
                'id' => '5',
                'name' => 'сом'
            ],
            [
                'id' => '6',
                'name' => 'р'
            ],
            [
                'id' => '7',
                'name' => 'манат'
            ],
            [
                'id' => '8',
                'name' => 'драм'
            ],
            [
                'id' => '9',
                'name' => 'сум'
            ],
            [
                'id' => '10',
                'name' => '€'
            ],
            [
                'id' => '11',
                'name' => '₦'
            ]

        ];

        foreach ($data as $item) {
            $repo->create($item);
        }

    }
}
