<?php

use Illuminate\Database\Seeder;
use App\Repositories\RoleGroupsRepository;

class RoleGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo = resolve(RoleGroupsRepository::class);

	    $data = [
            [   
                'id' => 1,
                'name' => 'Администраторы',
                'description' => 'Супер администраторы',
                'creator_organization_id' => '1'
		    ],
		    [   
                'id' => 2,
                'name' => 'КЦЖ',
                'description' => 'КЦЖ описание',
                'creator_organization_id' => '1'
		    ],
            [
                'id' => 3,
                'name' => 'Маркетологи',
                'description' => 'Маркетологи описание',
                'creator_organization_id' => '1'
            ],
            [
                'id' => 4,
                'name' => 'Скриптологи',
                'description' => 'Скриптологи описание',
                'creator_organization_id' => '1'
            ],
	    ];
    
	    foreach ($data as $item) {
       		$repo->create($item);
            }

        $max    = DB::table('role_group')->max('id') + 1;
        DB::statement('ALTER SEQUENCE role_group_id_seq RESTART WITH ' . $max . ';');
        

    }
}
