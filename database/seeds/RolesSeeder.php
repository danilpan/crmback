<?php

use Illuminate\Database\Seeder;
use App\Repositories\RolesRepository;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo = resolve(RolesRepository::class);

	    $data = [
		    [   
                'id' => 1,
                'group_id' => 2,
                'name' => 'Тимлидер',
                'description' => 'Тимлидер описание',
                'creator_organization_id' => '1'
		    ],
            [
                'id' => 2,
                'group_id' => 2,
                'name' => 'Оператор',
                'description' => 'Оператор описание',
                'creator_organization_id' => '1'
            ],
            [
                'id' => 3,
                'group_id' => 2,
                'name' => 'Стажер',
                'description' => 'Оператор новенький',
                'creator_organization_id' => '1'
            ],
            [
                'id' => 4,
                'group_id' => 3,
                'name' => 'Маркетолог старший',
                'description' => 'Маркетолого старший описание',
                'creator_organization_id' => '1'
            ],
            [
                'id' => 5,
                'group_id' => 3,
                'name' => 'Маркетолог младший',
                'description' => 'Маркетолого старший описание',
                'creator_organization_id' => '1'
            ],
            [
                'id' => 6,
                'group_id' => 4,
                'name' => 'Скриптолог',
                'description' => 'Скриптолог описание',
                'creator_organization_id' => '1'
            ],
            [   
                'id' => 7,
                'group_id' => 1,
                'name' => 'Администратор',
                'description' => 'Супер администратор',
                'creator_organization_id' => '1'
		    ]
            
	    ];
    
	    foreach ($data as $item) {
       		$repo->create($item);
            }

        $max    = DB::table('roles')->max('id') + 1;
        DB::statement('ALTER SEQUENCE roles_id_seq RESTART WITH ' . $max . ';');
            
    }
}
