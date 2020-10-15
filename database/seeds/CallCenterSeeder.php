<?php

use Illuminate\Database\Seeder;
use App\Repositories\CallCenterRepository;

class CallCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo    = resolve(CallCenterRepository::class);

        $data = [

            [
                'name' => 'CRM.pro',
            ],
            [
                'name' => 'MonsterLeads',
            ],
            [
                'name' => 'M1-shop',
            ]

        ];

        foreach ($data as $item) {
            $repo->create($item);
        }

    }
}
