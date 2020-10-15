<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'title',
        'api',
        'orders_data',
        'orders_fields',
        'organization_id',
        'order'
    ];

    protected $casts    = [
        'api'           => 'json',
        'orders_data'   => 'json',
        'order'   => 'json',
        'orders_fields' => 'json'
    ];

    const PERMISSION_FIELDS = [
        'api', 'orders_data', 'orders_fields','order'
    ];

    const DEFAULT   = [
        'api'   => [
            'organizations' => [
                'list'      => true,
                'view'      => true,
                'create'    => true,
                'update'    => true,
                'delete'    => true
            ],
            'products'  => [
                'list'      => true,
                'create'    => true,
                'update'    => true,
                'delete'    => true
            ],
            'permissions'   => [
                'list'      => true,
                'view'      => true,
                'create'    => true,
                'update'    => true,
                'delete'    => true
            ],
            'users'  => [
                'list'      => true,
                'create'    => true,
                'view'      => true,
                'update'    => true,
                'delete'    => true
            ],
        ],
        'order' => [
            'list'      => true,
            'list'      => true,
            'create'    => true,
            'update'    => true,
            'call'      => true, // Доступ на звонок из карточки заказа,
            'add_phone_black_list' => true, // Добавление номера телефона заказа в чёрный список
            'add_ip_black_list' => true, // Добавление ip адреса заказа в чёрный список
            'upsale_lvl2' => true, // показывать апсейлы второго уровня
            'sms_send' => true, // Отправка СМC
            'hide_phone'=>true,
            'delete'    => true
        ],
        'orders_data'   => [
            'hide_phone'=>true, // Скрывать номер телефона
            'hide_not_mine'=>true, // Скрывать заказы не обработанные под той учатной записью, под которой работает пользователь.
        ],
        'orders_fields' => [
            'key_lead'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'import_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'phone'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'site_product_name'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'country_code'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'webmaster_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'webmaster_transit_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'site_product_price'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'transit_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'webmaster_type'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'referer'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'description'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'real_profit'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'profit'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'request_hash'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'type'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'project_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'gasket_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'site_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'dial_time'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'dial_step'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'timezone'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'order_date'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'delivery_time'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'delivery_type'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'delivery_price'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'full_address'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'region'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'area'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'sity'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'street'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'home'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'room'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'postcode'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'is_unload'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'dop_info'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'flow_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'phone_2'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'phone_3'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'arrival_office_date'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'comment_client'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'phone_country'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'order_age'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'manager_id'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'cost_main'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ],
            'sales'   => [
                'view'      => true,
                'sort'      => true,
                'filter'    => true
            ]
        ],
    ];
}