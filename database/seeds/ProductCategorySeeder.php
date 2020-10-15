<?php

use App\Services\ProductCategoryService;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run()
    {

        $service    = resolve(ProductCategoryService::class);

        $data = [

            [
                'name' => 'Category 1',
                'organization_id' => 1,
                'is_work' => 1,
            ],
            [
                'name' => 'Category 2',
                'organization_id' => 1,
                'is_work' => 1,
            ],
            [
                'name' => 'Category 3',
                'organization_id' => 1,
                'is_work' => 1,
            ],
            [
                'name' => 'Category 4',
                'organization_id' => 1,
                'is_work' => 1,
            ],
            [
                'name' => 'Category 5',
                'organization_id' => 1,
                'is_work' => 1,
            ],
            [
                'name' => 'Category 6',
                'organization_id' => 1,
                'is_work' => 1,
            ],
            [
                'name' => 'Category 7',
                'organization_id' => 1,
                'is_work' => 1,
            ],
            [
                'name' => 'Category 8',
                'organization_id' => 1,
                'is_work' => 1,
            ]

        ];

        foreach ($data as $item) {
            $service->create($item);
        }

    }

}