<?php

use Illuminate\Database\Seeder;
use App\Services\UnloadsService;

class UnloadsSeeder extends Seeder
{

    public function run()
    {
        $repo    = resolve(UnloadsService::class);
        $data    = [
            [
                'organization_id'      => 1,
                'name'                 => 'Выгрузка 1',
                'comment'              => 'Автообзвон КЗ',
                'config'               => '{data: {}}',
                'is_work'              => 1
            ],
            [
                'organization_id'      => 1,
                'name'                 => 'Выгрузка 2',
                'comment'              => 'Автообзвон КЗ',
                'config'               => '{data: {}}',
                'is_work'              => 1
            ],
            [
                'organization_id'      => 1,
                'name'                 => 'Выгрузка 3',
                'comment'              => 'Автообзвон КЗ',
                'config'               => '{data: {}}',
                'is_work'              => 1
            ],
            [
                'organization_id'      => 1,
                'name'                 => 'Выгрузка 4',
                'comment'              => 'Автообзвон КЗ',
                'config'               => '{data: {}}',
                'is_work'              => 1
            ]
        ];

        foreach ($data as $item) {
            $repo->create($item);
        }

    }
}
