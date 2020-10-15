<?php

use Illuminate\Database\Seeder;
use App\Services\OrdersService;

use App\Repositories\DeliveryTypesRepository;

class DeliveryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service    = resolve(DeliveryTypesRepository::class);
        $data       = [
            [
                'deliveryType' => [
                	'name' => 'Тестовая доставка',
                	'id_organization' => '67',
                	'price' => '248',
                	'is_work' => '1',
                    'priority' => '3',
                    'peoples' => null,
                	'postcode_info' => 'ed knken nfee nenenenfje nfejfnjeefnejfefeefneb  b heb fbe ed knken nfee nenenenfje nfejfnjeefnejfefeefneb  b heb fbe ed knken nfee nenenenfje nfejfnjeefnejfefeefneb  b heb fbe'
                ]
            ],
            [
                'deliveryType' => [
                	'name' => 'Тестовая доставка2',
                	'id_organization' => '68',
                	'price' => '500',
                	'is_work' => '1',
                    'priority' => '3',
                	'peoples' => null,
                	'postcode_info' => 'ed knken nfee nenenenfje nfejfnjeefnejfefeefneb  b heb fbeed knken nfee nenenenfje nfejfnjeefnejfefeefneb  b heb fbe'
                ]
            ],
            [
                'deliveryType' => [
                	'name' => 'Тестовая доставка',
                	'id_organization' => '49',
                	'price' => '551',
                	'is_work' => '1',
                    'priority' => '3',
                	'peoples' => null,
                	'postcode_info' => 'ed knken nfee nenenenfje nfejfnjeefnejfefeefneb  b heb fbe'
                ]
            ],
        ];

        foreach ($data as $d) {
            $deliveryType  = $service->create($d['deliveryType']);
        }
    }
}
