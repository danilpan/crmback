<?php

use App\Repositories\OrderAdvertSourceRepository;
use Illuminate\Database\Seeder;

class OrderAdvertSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo = resolve(OrderAdvertSourceRepository::class);

        $data = [
            [
                'order_advert_source' => [
                    'name' => 'Instagram',
                    'is_show' => '1'
                ],
            ],
            [
                'order_advert_source' => [
                    'name' => 'Facebook',
                    'is_show' => '1'
                ],
            ],
            [
                'order_advert_source' => [
                    'name' => 'Одноклассники',
                    'is_show' => '1'
                ],
            ],
            [
                'order_advert_source' => [
                    'name' => 'Мой мир',
                    'is_show' => '1'
                ],
            ],
            [
                'order_advert_source' => [
                    'name' => 'Вконтакте',
                    'is_show' => '1'
                ],
            ],
            [   'order_advert_source' => [
                    'name' => 'SMS-рассылка',
                    'is_show' => '1'
                ],
            ],
            [    'order_advert_source' => [
                    'name' => 'Email-рассылка',
                    'is_show' => '1'
                ],
            ],
            [   'order_advert_source' => [
                    'name' => 'Всплывающее оповещение в браузере',
                    'is_show' => '1'
                ],
            ],
            [    'order_advert_source' => [
                    'name' => 'Заказ с сайта',
                    'is_show' => '1'
                ],
            ],
            [    'order_advert_source' => [
                    'name' => 'Olx',
                    'is_show' => '1'
               ]
            ]
        ];

        foreach ($data as $d){
            $repo->create($d['order_advert_source']);
        }
    }
}
