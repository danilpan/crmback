<?php

use App\Repositories\DeviceTypeRepository;

/**
 * Created by PhpStorm.
 * User: timur
 * Date: 18.02.19
 * Time: 23:01
 */

class DeviceTypeSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo = resolve(DeviceTypeRepository::class);

        $data = [
            [
                'device_type' => [
                    'name' => 'С телефона или планшета',
                    'is_show' => '1'
                ],
            ],
            [
                'device_type' => [
                    'name' => 'С ПК',
                    'is_show' => '1'
                ],
            ]
        ];

        foreach ($data as $d) {
            $repo->create($d['device_type']);
        }
    }
}