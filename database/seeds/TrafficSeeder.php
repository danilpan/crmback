<?php

use Illuminate\Database\Seeder;
use App\Repositories\TrafficsRepository;

class TrafficSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            $repo = resolve(TrafficsRepository::class);

	    $data = [
		    [
			'name' => 'Контекстная реклама',
		    ],
		    [
			'name' => 'Таргетированная реклама',
		    ],
		    [
			'name' => 'Социальные сети',
		    ],
		    [
			'name' => 'Дорвеи',
		    ],

	    ];
    
	    foreach ($data as $item) {
       		$repo->create($item);
            }

    }
}
