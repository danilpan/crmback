<?php

use App\Models\DeviceType;
use App\Models\OrderAdvertSource;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Order;
use App\Models\Site;
use App\Models\Phone;


class OrdersSeeder extends Seeder
{
    protected $projectTitles    = [
        'Японский бинокль canon 60x60 нож gerber bear grylls ultimate kz',
        'Колонка jbl charge2+ ru',
        'Мини проектор yg-300 ru',
        'Ортопедическое белье activemax uz',
        'Jbl pulse 3 - портативная акустическая система ru',
        'Beats россия Тв-шоп 1 Сексшоп Тв-шоп 2 Beats 17регионов Гироскутеры россия',
        'Видеорегистратор hd smart kz',
        'Гироскутеры россия',
        'Средство для домашнего отбеливания зубов white light ru',
        'Японский бинокль canon 60x60 в подарок часы swiss army ru',
        'Экономитель топлива fuelfree kz',
        'Браслеты nomination italy ru'
    ];

    protected function getProjects($organization)
    {
        $total      = 5;
        $projects   = [];
        for($i=0; $i<$total; $i++) {
            $project    = Project::create([
                'title'             => $this->projectTitles[rand(0, count($this->projectTitles) - 1)],
                'description'       => $this->facker->text(2000),
                'organization_id'   => $organization->id
            ]);


            $data   = [
                'id'    => $project->id,
                'sites' => []
            ];

            $totalSites = 10;
            for($k=0; $k<$totalSites; $k++) {
                $site   = Site::create([
                    'url'               => $this->facker->url,
                    'organization_id'   => $organization->id,
                    'project_id'        => $project->id
                ]);

                $data['sites'][]    = $site->id;
            }

            $projects[] = $data;
        }

        return $projects;
    }

    protected function getPhone($organization, $project)
    {
        $phone  = Phone::create([
            'phone' => $this->facker->e164PhoneNumber
        ]);

        return $phone;
    }

    protected function getSource(){
        $source = OrderAdvertSource::all()->random(1)->first();
        $source_id = $source->getAttribute('id');

        return $source_id;
    }

    protected function getDevice(){
        $device = DeviceType::all()->random(1)->first();
        $device_id = $device->getAttribute('id');

        return $device_id;
    }


    public function run()
    {
        $organizations      = Organization::whereNull('parent_id')->get();
        $this->facker       = $faker = Faker\Factory::create('ru_RU');

        $total  = 115;


        foreach ($organizations as $organization) {
            $projects   = $this->getProjects($organization);

            for($i=0; $i<$total; $i++) {
                $source_id = $this->getSource();
                $device_id = $this->getDevice();
                $project    = $projects[rand(0, count($projects) - 1)];
                $siteId     = $project['sites'][rand(0, count($project['sites']) - 1)];

//                $phone      = $this->getPhone($organization, $project);

                $order      = Order::create([
                    'organization_id'   => $organization->id,
                    'key'               => substr(md5(uniqid()), 0, 10),
//                    'project_id'        => $project['id'],
//                    'site_id'           => $siteId,
                    'phones'            => [$this->facker->e164PhoneNumber],
//                    'phone_id'          => $phone->id
                    'source_id'         => $source_id,
                    'device_id'         => $device_id
                ]);

                $order->sites()->attach($siteId);
                $order->projects()->attach($project['id']);

//                $order->phones()->attach($phone);
            }
        }


//        $service    = resolve(OrdersService::class);
//        $data       = [
//            [
//                'order' => [
//                    'id'                    => null,
//                    'import_id'             => 'reklpro_1793704',
//                    'phone'                 => '77083666483',
//                    'site_product_name'     => 'Ортопедическое белье ACTIVEMAX',
//                    'webmaster_id'          => '224',
//                    'webmaster_transit_id'  => null,
//                    'site_product_price'    => '13990',
//                    'description'           => '{"fio":"Баймаганбетов Бекзат","phone":"77083666483","metrika_id":null,"google_id":null,"retarg_vk_id":null,"mail_id":null,"fb_id":"334724903687008"}',
//                    'transit_id'            => '9859226',
//                    'country_code'          => 'KZ',
//                    'webmaster_type'        => '1',
//                    'profit'                => 500,
//                    'real_profit'           => 500,
//                    'request_hash'          => 'fdeb585a06e93816e38c595c665beb7a',
//                    'type'                  => 'api'
//                ],
//                'project'   => [
//                    'id'                => null,
//                    'import_id'         => 'reklpro_kz_id_482',
//                    'name_for_client'   => 'Ортопедическое белье ACTIVEMAX',
//                    'desc'              => 'Ортопедическое белье ACTIVEMAX',
//                    'is_work'           => null,
//                    'hold'              => '0',
//                    'name'              => 'Ортопедическое белье ACTIVEMAX',
//                    'sex'               => '0',
//                    'countries'         => 'Казахстан',
//                    'prognos'           => null,
//                    'parent_id'         => null
//                ],
//                'site'  => [
//                    'id'            => null,
//                    'import_id'     => 'reklpro_kz_id_1932',
//                    'title'         => 'KZ_Ортопедическое белье ACTIVEMAX _Лендинг А - (универсальный под СНГ)(Мобильный)',
//                    'project_id'    => 'reklpro_kz_id_482',
//                    'url'           => 'hit-prodazh.pro/activemax'
//                ],
//                'gasket'    => [
//
//                ]
//            ],
//            [
//                'order' => [
//                    'id'                    => null,
//                    'import_id'             => 'reklpro_1793705',
//                    'phone'                 => '77083666484',
//                    'site_product_name'     => 'Ортопедическое белье ACTIVEMAX-2',
//                    'webmaster_id'          => '225',
//                    'webmaster_transit_id'  => null,
//                    'site_product_price'    => '13991',
//                    'description'           => '{"fio":"Азимов Серик","phone":"7708366648","metrika_id":null,"google_id":null,"retarg_vk_id":null,"mail_id":null,"fb_id":"334724903687009"}',
//                    'transit_id'            => '9859227',
//                    'country_code'          => 'KZ',
//                    'webmaster_type'        => '1',
//                    'profit'                => 600,
//                    'real_profit'           => 600,
//                    'request_hash'          => 'fdeb525a06e93816e38c595c665beb7a',
//                    'type'                  => 'api'
//                ],
//                'project'   => [
//                    'id'                => null,
//                    'import_id'         => 'reklpro_kz_id_483',
//                    'name_for_client'   => 'Ортопедическое белье ACTIVEMAX-2',
//                    'desc'              => 'Ортопедическое белье ACTIVEMAX-2',
//                    'is_work'           => null,
//                    'hold'              => '0',
//                    'name'              => 'Ортопедическое белье ACTIVEMAX-2',
//                    'sex'               => '0',
//                    'countries'         => 'Казахстан',
//                    'prognos'           => null,
//                    'parent_id'         => null
//                ],
//                'site'  => [
//                    'id'            => null,
//                    'import_id'     => 'reklpro_kz_id_1934',
//                    'title'         => 'KZ_Ортопедическое белье ACTIVEMAX-2 Лендинг Б - (универсальный под СНГ)(Мобильный)',
//                    'project_id'    => 'reklpro_kz_id_483',
//                    'url'           => 'hit-prodazh.pro/activemax-2'
//                ],
//                'gasket'    => [
//
//                ]
//            ],
//            [
//                'order' => [
//                    'id'                    => null,
//                    'import_id'             => 'reklpro_1793706',
//                    'phone'                 => '77083666485',
//                    'site_product_name'     => 'Ортопедическое белье ACTIVEMAX-3',
//                    'webmaster_id'          => '226',
//                    'webmaster_transit_id'  => null,
//                    'site_product_price'    => '13992',
//                    'description'           => '{"fio":"Иванов Иван","phone":"77083666485","metrika_id":null,"google_id":null,"retarg_vk_id":null,"mail_id":null,"fb_id":"334724903687107"}',
//                    'transit_id'            => '9859228',
//                    'country_code'          => 'KZ',
//                    'webmaster_type'        => '1',
//                    'profit'                => 400,
//                    'real_profit'           => 400,
//                    'request_hash'          => 'fdeb525a06e93828e38c595c665bebDa',
//                    'type'                  => 'api'
//                ],
//                'project'   => [
//                    'id'                => null,
//                    'import_id'         => 'reklpro_kz_id_484',
//                    'name_for_client'   => 'Ортопедическое белье ACTIVEMAX-3',
//                    'desc'              => 'Ортопедическое белье ACTIVEMAX-3',
//                    'is_work'           => null,
//                    'hold'              => '0',
//                    'name'              => 'Ортопедическое белье ACTIVEMAX-3',
//                    'sex'               => '0',
//                    'countries'         => 'Казахстан',
//                    'prognos'           => null,
//                    'parent_id'         => null
//                ],
//                'site'  => [
//                    'id'            => null,
//                    'import_id'     => 'reklpro_kz_id_1935',
//                    'title'         => 'KZ_Ортопедическое белье ACTIVEMAX-3 Лендинг С - (универсальный под СНГ)(Мобильный)',
//                    'project_id'    => 'reklpro_kz_id_484',
//                    'url'           => 'hit-prodazh.pro/activemax-3'
//                ],
//                'gasket'    => [
//
//                ]
//            ],
//            [
//                'order' => [
//                    'id'                    => null,
//                    'import_id'             => 'reklpro_1793707',
//                    'phone'                 => '77083666487',
//                    'site_product_name'     => 'Ортопедическое белье ACTIVEMAX-4',
//                    'webmaster_id'          => '226',
//                    'webmaster_transit_id'  => null,
//                    'site_product_price'    => '13992',
//                    'description'           => '{"fio":"Иванова Марина","phone":"77083666487","metrika_id":null,"google_id":null,"retarg_vk_id":null,"mail_id":null,"fb_id":"332524903687107"}',
//                    'transit_id'            => '9859229',
//                    'country_code'          => 'KZ',
//                    'webmaster_type'        => '1',
//                    'profit'                => 300,
//                    'real_profit'           => 300,
//                    'request_hash'          => 'fdeb525a06e93828e38c745c665bebDa',
//                    'type'                  => 'api'
//                ],
//                'project'   => [
//                    'id'                => null,
//                    'import_id'         => 'reklpro_kz_id_485',
//                    'name_for_client'   => 'Ортопедическое белье ACTIVEMAX-4',
//                    'desc'              => 'Ортопедическое белье ACTIVEMAX-4',
//                    'is_work'           => null,
//                    'hold'              => '0',
//                    'name'              => 'Ортопедическое белье ACTIVEMAX-4',
//                    'sex'               => '0',
//                    'countries'         => 'Казахстан',
//                    'prognos'           => null,
//                    'parent_id'         => null
//                ],
//                'site'  => [
//                    'id'            => null,
//                    'import_id'     => 'reklpro_kz_id_1936',
//                    'title'         => 'KZ_Ортопедическое белье ACTIVEMAX-4 Лендинг С - (универсальный под СНГ)(Мобильный)',
//                    'project_id'    => 'reklpro_kz_id_485',
//                    'url'           => 'hit-prodazh.pro/activemax-4'
//                ],
//                'gasket'    => [
//
//                ]
//            ]
//
//        ];
//
//        foreach ($data as $d) {
//            $order  = $service->create($d['order'], $d['gasket'], $d['project'], $d['site']);
//        }
    }
}
