<?php

use Illuminate\Database\Seeder;
use App\Repositories\ProductsRepository;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        $repo    = resolve(ProductsRepository::class);

        $data = [

            [
                'code_product' => 'p1',
                'uniqued_import_id' => 'p2',
                'organization_id' => 1,
                'cat_id' => 1,
                'article' => 'product1',
                'img' => 'productImg1.png',
                'parent_project' => 'Products',
                'parent_site' => 'ProductSite',
                'name' => 'Super product 1',
                'price_cost' => 1000,
                'price_online' => 1500,
                'price_prime' => 1800,
                'weight' => '15',
                'desc' => 'Description Super product 1',
                'script' => 'super script 1',
                'basic_unit' => 'Product 1 unit',
                'nabor' => 1,
                'service' => 1,
                'complect' => 2,
                'basic_unit_seat' => 'Product 1',
                'is_work' => 1
            ],
            [
                'code_product' => 'p2',
                'uniqued_import_id' => 'p3',
                'organization_id' => 2,
                'cat_id' => 2,
                'article' => 'product2',
                'img' => 'productImg2.png',
                'parent_project' => 'Products',
                'parent_site' => 'ProductSite',
                'name' => 'Super product 2',
                'price_cost' => 1000,
                'price_online' => 1500,
                'price_prime' => 1800,
                'weight' => '15',
                'desc' => 'Description Super product 2',
                'script' => 'super script 2',
                'basic_unit' => 'Product 2 unit',
                'nabor' => 1,
                'service' => 1,
                'complect' => 2,
                'basic_unit_seat' => 'Product 2',
                'is_work' => 1
            ],
            [
                'code_product' => 'p3',
                'uniqued_import_id' => 'p4',
                'organization_id' => 3,
                'cat_id' => 3,
                'article' => 'product3',
                'img' => 'productImg3.png',
                'parent_project' => 'Products',
                'parent_site' => 'ProductSite',
                'name' => 'Super product 3',
                'price_cost' => 1000,
                'price_online' => 1500,
                'price_prime' => 1800,
                'weight' => '15',
                'desc' => 'Description Super product 3',
                'script' => 'super script 3',
                'basic_unit' => 'Product 3 unit',
                'nabor' => 1,
                'service' => 1,
                'complect' => 2,
                'basic_unit_seat' => 'Product 3',
                'is_work' => 1
            ],
            [
                'code_product' => 'p4',
                'uniqued_import_id' => 'p5',
                'organization_id' => 4,
                'cat_id' => 4,
                'article' => 'product4',
                'img' => 'productImg4.png',
                'parent_project' => 'Products',
                'parent_site' => 'ProductSite',
                'name' => 'Super product 4',
                'price_cost' => 1000,
                'price_online' => 1500,
                'price_prime' => 1800,
                'weight' => '15',
                'desc' => 'Description Super product 4',
                'script' => 'super script 4',
                'basic_unit' => 'Product 4 unit',
                'nabor' => 1,
                'service' => 1,
                'complect' => 2,
                'basic_unit_seat' => 'Product 4',
                'is_work' => 1
            ]

        ];

        foreach ($data as $item) {
            $repo->create($item);
        }
        
    }
}