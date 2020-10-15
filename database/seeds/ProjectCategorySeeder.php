<?php

use Illuminate\Database\Seeder;
use App\Repositories\ProjectCategoryRepository;

class ProjectCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $repo = resolve(ProjectCategoryRepository::class);

	    $data = [
		    [
			'name' => 'Средства для волос',
			'organization_id' => '1',
			'is_work' => true
		    ],
		    [
			'name' => 'Средства для тела',
			'organization_id' => '2',
			'is_work' => false
		    ],
		    [
			'name' => 'Средства для лица',
			'organization_id' => '3',
			'is_work' => true
		    ],
		    [
			'name' => 'Фитнес одежда',
			'organization_id' => '4',
			'is_work' => true
		    ],

	    ];
    
	    foreach ($data as $item) {
       		$repo->create($item);
            }


    }
}
