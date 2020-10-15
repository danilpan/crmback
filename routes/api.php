<?php

Route::group(['prefix' => 'v1', 'namespace' => 'Api\V1'], function(){
    Route::get('orders',            'OrdersController@getList');
    Route::get('orders/{id}',       'OrdersController@getById');
    Route::post('orders',           'OrdersController@create');
    Route::put('orders/{id}',       'OrdersController@update');
    Route::delete('orders/{id}',    'OrdersController@delete');
});


Route::group(['prefix' => 'v2', 'namespace' => 'Api\V2'], function(){

        Route::post('users/auth',                   'UsersController@auth');

        Route::get('unloads/api',                   'UnloadsController@api');
        Route::post('unloads/api',                  'UnloadsController@apiPOST');
        Route::post('unloads/webinar_api',          'UnloadsController@webinarAPI');

        Route::post('orders/public/create',         'OrdersController@publicCreate');
        Route::get('orders/public/status',          'OrdersController@publicStatus');        
        Route::get('orders/public/ats_queues',      'OrdersController@getAtsQueues');

        Route::get('test/test_commands',      'TestCommandsController@getList');

        /*******************************
         *        OrdersDialSteps      *
         *******************************/
         /**
          * Создает шаг перезвона заказа 
          * @param integer  queue_id            Айди очереди в которой производился вызов
          * @param integer  order_id            Айди заказа
          * @param integer  dial_step           Шаг перезвона
          * @param integer  dial_time           Время когда должен совершиться звонок
          * @example {
          *              "queue_id": 1,
          *              "order_id": 1412433,
          *              "dial_step": 1,
          *              "dial_time": "2018-04-19 11:44:09"
          *          }
          */

        // Создает новый или обновляет по уникальности полей queue_id и order_id
        Route::post('orders_dial_step/create_or_update', 'OrdersDialStepsController@createOrUpdate');

        /*******************************
         *        addCall      *
         *******************************/
         /**
          * Добавляет звонок
          * @param string  call_id            Идентификатор звонка.
          * @param string  api_key            Апи-ключ.
          * @param string  phone              Номер телефона клиента.
          * @param string  phone_tor          Каллер айди с которого совершался вызов.
          * @param string  type               Тип звонка (auto/in/out)
          * @param string  sip                Сип юзера АТС (Если он есть.)
          * @param integer  rule_id           Маршрут через который совершается вызов (Если он есть)
          * @param integer  queue_id          Очередь в которой совершается вызов. (Если она есть)
          * @param integer  id_order          Айди заказа к которому звонок привязан (Параметр может и не приходить.)          * 
          */

        // Создает новый звонок
        Route::get('calls/add_call',                'CallsController@addCall');
        Route::post('calls/add_call_status',                'CallsController@addCallStatus');
        Route::get('calls/public/get_call_status',                'CallsController@publicGetList');
        
        Route::post('user_status_logs/refresh', 'UserStatusLogController@refresh');


        Route::post('attempts',                      'AttemptsController@create');
        Route::post('attempts/is_plate_registered',    'AttemptsController@isPlateRegistered');


        Route::group(['middleware' => ['auth.jwt']], function(){

        Route::get('attempts',                       'AttemptsController@getList');
        Route::get('attempts/{id}',                  'AttemptsController@getById');

        
        Route::get('calls/go_operator_call/{order_id}/{phone}', 'CallsController@goOperatorCall');

        /*******************************
         *        LogActivity          *
         *******************************/
        Route::get('log_activities/list',           'LogActivitiesController@getList');
         /**
          * Добавление логов
          * @param string  action            
          * @param string  info
          * @example {
          *              "action": "logout",
          *              "info": "{login:login}"
          *          }
          */

        // Создает новый или обновляет по уникальности полей queue_id и order_id
        Route::post('log_activities/add_to_log',     'LogActivitiesController@addToLog');

        
        Route::get('calls/do_call',                 'CallsController@doCall');
        Route::get('calls',                         'CallsController@getList');
        Route::post('calls/export',                 'CallsController@export');        



        Route::get('dx_users',                      'UsersController@dxUsers');
        Route::get('users/me',                      'UsersController@getMe');
        Route::get('users/{id}',                    'UsersController@getById');
        Route::get('users/{id}/search_users',       'UsersController@searchUsers');

        /**
         * Создает нового пользователя
         * @param string  "first_name",
         * @param string  "last_name",
         * @param string  "middle_name",
         * @param string  "login",
         * @param string  "phone",
         * @param string  "mail",
         * @param boolean "is_work",
         * @param boolean "out_calls",
         * @param string  "phone_office",
         * @param integer "organization_id",
         * @param string  "telegram",
         * @param string password - пароль (должен не совпадать со старым, состоять из 8-ми знаков, содержать спецсимволы([@$!%*#?&_]),
         *                                              цифры, латинские буквы в малом и большом регистре)
         * @param string password_confirmation - должен совпадать с password
         * @example {
                  "first_name" : "Sergei",
                  "last_name": "Sergeev",
                  "middle_name": "Sergeevich,
                  "login": "sergei",
                  "phone": "77471234567,
                  "mail": "sergei@gmail.com,
                  "is_work": true,
                  "out_calls": true,
                  "phone_office": "87273456789",
                  "organization_id": 1,
                  "telegram": "t.me/sergei",
                  "password":"Password_12345",
                  "password_confirmation":"Password_12345"
                   }
         */
        Route::post('users',                        'UsersController@create');

        /**
         * Обновляет данные пользователя
         * @param string "first_name",
         * @param string "last_name",
         * @param string "middle_name",
         * @param string "login",
         * @param string "phone",
         * @param string "mail",
         * @param boolean "is_work",
         * @param boolean "out_calls",
         * @param string  "phone_office",
         * @param integer "organization_id",
         * @param string  "telegram"
         * @example {
                    "first_name" : "Sergei",
                    "last_name": "Sergeev",
                    "middle_name": "Sergeevich,
                    "login": "sergei",
                    "phone": "77471234567,
                    "mail": "sergei@gmail.com,
                    "is_work": true,
                    "out_calls": true,
                    "phone_office": "87273456789",
                    "organization_id": 1,
                    "telegram": "t.me/sergei",
                    "password":"Password_12345",
                    "password_confirmation":"Password_12345"
                    }
         */
        Route::put('users/{id}',                    'UsersController@update');
        Route::put('users/{id}/set_show_is_work',   'UsersController@setShowIsWork');

        /**
         * Возвращает данные об авторизованном пользователе
         */
        Route::get('profile/me',                    'UsersController@getProfile');

        /**
         * Обновляет данные авторизованного пользователя
         * @param integer "id"  -  id пользователя
         * @param string "first_name",
         * @param string "last_name",
         * @param string "middle_name",
         * @param string "login",
         * @param string "phone",
         * @param string "mail",
         * @param boolean "is_work",
         * @param boolean "out_calls",
         * @param string  "phone_office",
         * @param integer "organization_id",
         * @param string  "telegram"
         * @example {
                    "id" : "123"
                    "first_name" : "Sergei",
                    "last_name": "Sergeev",
                    "middle_name": "Sergeevich,
                    "login": "sergei",
                    "phone": "77471234567,
                    "mail": "sergei@gmail.com,
                    "telegram": "t.me/sergei",
                    "user_images[i][image_upload]": FormData{image file),
                    "user_images[i][is_main]": "true"
                    }
         */
        Route::put('profile/me',                    'UsersController@updateProfile');

        /**
         * Обновляет старый пароль пользователя
         * @param string current_password - действующий пароль
         * @param string new_password - новый пароль (должен не совпадать со старым, состоять из 8-ми знаков, содержать спецсимволы([@$!%*#?&_]),
         *                                              цифры, латинские буквы в малом и большом регистре)
         * @param string new_password_confirmation - должен совпадать с new_password
         * @example {
                    "current_password":"Password_123",
                    "new_password":"Password_12345",
                    "new_password_confirmation":"Password_12345"
                    }
         */
        Route::put('users/{id}/password_update',    'UsersController@updatePassword');

        //Сбросс сессий авторизаций по role_id
        Route::get('users/{role_id}/logout_by_role',     'UsersController@logoutByRoleChange');

        Route::get('organizations/{id}/users',      'UsersController@getByOrganization');

        
        Route::get('orders/sales_report_delates',   'OrdersController@salesReportDelates');
        Route::get('orders/sales_report',           'OrdersController@salesReport');
        Route::post('orders/export',                'OrdersController@exToExcel');
        Route::get('orders',                        'OrdersController@getList');
        Route::get('orders/{key}',                  'OrdersController@getByKey');
        Route::put('orders/change_m/{key}',        'OrdersController@changeManagerId');
        Route::post('orders',                       'OrdersController@create');
        Route::put('orders/{id}',                   'OrdersController@update');
        Route::delete('orders/{id}',                'OrdersController@delete');
        Route::get('order_doubles/{key}',           'OrdersController@getDoubles');
        Route::get('orders/get_by_operator/{operator_id}', 'OrdersController@getByOperator');
        Route::post('orders/refuse_order/{id}',     'OrdersController@refuseOrder');

        Route::get('unloads/dx_search',             'UnloadsController@dxSearch');
        Route::get('unloads',                       'UnloadsController@getList');
        Route::get('unloads/{id}',                  'UnloadsController@getById');
        Route::post('unloads',                      'UnloadsController@create');
        Route::put('unloads/{id}',                  'UnloadsController@update');
        Route::delete('unloads/{id}',               'UnloadsController@delete');

        Route::post('products/export',    'ProductsController@exToExcel');
        Route::get('products',            'ProductsController@getList');
        Route::get('products/suggest',    'ProductsController@getSuggest');
        Route::get('products/{id}',       'ProductsController@getById');
        Route::post('products',           'ProductsController@create');
        Route::put('products/{id}',       'ProductsController@update');
        Route::delete('products/{id}',    'ProductsController@delete');

        Route::get('statuses/get_by_key/{id}', 'StatusesController@getList');
        Route::get('statuses/{id}',            'StatusesController@getById');
        Route::post('statuses',                'StatusesController@create');
        Route::post('statuses/get_all',        'StatusesController@getAll');
        Route::post('statuses/get_tree',       'StatusesController@getTree');
        
        Route::put('statuses/{id}',            'StatusesController@update');
        Route::delete('statuses/{id}',         'StatusesController@delete');

        Route::post('order_advert_sources/export',    'OrderAdvertSourceController@exToExcel');
        Route::get('order_advert_sources',            'OrderAdvertSourceController@getList');
        Route::get('order_advert_sources/{id}',       'OrderAdvertSourceController@getById');
        Route::get('order_advert_sources/order/{id}', 'OrderAdvertSourceController@getByOrderKey');
        Route::post('order_advert_sources',           'OrderAdvertSourceController@create');
        Route::put('order_advert_sources/{id}',       'OrderAdvertSourceController@update');
        Route::delete('order_advert_sources/{id}',    'OrderAdvertSourceController@delete');

        /**
         * Экспорт таблицы в эксель
         */
        Route::post('order_senders/export',                    'OrderSenderController@exToExcel');
        /**
         * Возвращает список всех доступных отправителей
         */
        Route::get('order_senders',                            'OrderSenderController@getList');
        /**
         * Возвращает отправителя с указанным айди
         * @param integer id Айди отправителя
         */
        Route::get('order_senders/{id}',                       'OrderSenderController@getById');
        /**
         * Создает новую запись в таблице order_senders
         * @param integer organization_id Айди организации отправителя
         * @param string iin ИИН отправителя (уникальный)
         * @param string phone номер телефона отправителя
         * @param boolean is_work Активен ли отправитель
         * @example {
                    "organization_id":1,
                    "name":"Санжар Мухамеджанов",
                    "iin":"920317301065",
                    "phone":"87771473737",
                    "is_work":true
                    }
         */
        Route::post('order_senders',                           'OrderSenderController@create');
        /**
         * Обновляет данные отправителя с указанным айди
         * @param integer id Айди отправителя
         */
        Route::put('order_senders/{id}',                       'OrderSenderController@update');
        /**
         * Удаляет из таблицы отправителя с указанным айди
         * @param integer id Айди отправителя
         */
        Route::delete('order_senders/{id}',                    'OrderSenderController@delete');

        /*******************************
         *          SmsTemplate        *
         *******************************/
        /**
         * Создает новую запись в таблице Шаблонов СМС
         * @param integer organization_id ID организации, к которой привяжется создаваемый шаблон
         * @param string name Имя шаблона
         * @param string sms_text Текст СМС
         * @param boolean is_work Включен ли шаблон
         * @example {
         *              "organization_id":123,
         *              "name":"Заявка принята",
         *              "sms_text":"Ваша заявка принята!",
         *              "is_work":true
         *          }
         */
        Route::post('sms_templates',                'SmsTemplatesController@create');
        /**
         * Возвращает СМС шаблоны с учетом переменных
         *
         */
        Route::get('sms_templates/order/{id}',      'SmsTemplatesController@getByOrderKey');
        /**
         * Возвращает список всех доступных шаблонов СМС
         */
        Route::get('sms_templates',                 'SmsTemplatesController@getList');
        /**
         * Возвращает шаблон с указанным id
         */
        Route::get('sms_templates/{id}',            'SmsTemplatesController@getById');
        /**
         * Обновляет данные шаблона с указанным айди
         * @param integer id Айди шаблона
         */
        Route::put('sms_templates/{id}',            'SmsTemplatesController@update');
        /**
         * Удаляет из таблицы СМС шаблон с указанным айди
         * @param integer id Айди шаблона
         */
        Route::delete('sms_templates/{id}',         'SmsTemplatesController@delete');

        Route::post('device_types/export',    'DeviceTypeController@exToExcel');
        Route::get('device_types',            'DeviceTypeController@getList');
        Route::get('device_types/{id}',       'DeviceTypeController@getById');
        Route::get('device_types/order/{id}', 'DeviceTypeController@getByOrderKey');
        Route::post('device_types',           'DeviceTypeController@create');
        Route::put('device_types/{id}',       'DeviceTypeController@update');
        Route::delete('device_types/{id}',    'DeviceTypeController@delete');

        Route::post('delivery_types/export',     'DeliveryTypesController@exToExcel');
        Route::get('delivery_types',            'DeliveryTypesController@getList');
        Route::get('delivery_types/{id}',       'DeliveryTypesController@getById');
        Route::get('delivery_types/order/{id}', 'DeliveryTypesController@getByOrderKey');
        Route::post('delivery_types',           'DeliveryTypesController@create');
        Route::put('delivery_types/{id}',       'DeliveryTypesController@update');
        Route::delete('delivery_types/{id}',    'DeliveryTypesController@delete');        

        Route::post('postcode_info',            'PostcodeInfoController@create');
        Route::get('postcode_info/reindex',     'PostcodeInfoController@reindex');
        Route::get('postcode_info/get_one',     'PostcodeInfoController@getOne');

        Route::post('projects/export',          'ProjectsController@exToExcel');
        Route::get('projects',                  'ProjectsController@getList');
        Route::get('projects_with_pages',       'ProjectsController@getListWithPages');
        Route::get('projects/suggest',          'ProjectsController@getSuggest');
        Route::get('projects/{id}',             'ProjectsController@getById');
        Route::post('projects',                 'ProjectsController@create');
        Route::put('projects/{id}',             'ProjectsController@update');
        Route::delete('projects/{id}',          'ProjectsController@delete');
        Route::post('projects/products/{id}',   'ProjectsController@addProducts');
        Route::get('projects/products/{id}',    'ProjectsController@getProducts');
        Route::delete('projects/products/{id}/{project_id}',       'ProjectsController@deleteProducts');        

        Route::get('get_address_list',            'GetAddressController@getList');
        Route::get('get_warehouse_list',          'GetAddressController@getListWarehouse');

        Route::post('black_list',                  'BlackListController@create');
        Route::delete('black_list/{id}',           'BlackListController@delete');

        Route::get('dx_geo',                    'GeoController@dxGeos');
        Route::get('geo',                       'GeoController@getList');
        Route::get('geo/{id}',                  'GeoController@getById');
        Route::get('geo/phone/{phone}',         'GeoController@getByPhone');
        Route::post('geo',                      'GeoController@create');
        Route::put('geo/{id}',                  'GeoController@update');
        Route::delete('geo/{id}',               'GeoController@delete');

        Route::get('sms',            'SmsController@getList');
        Route::get('sms/{id}',       'SmsController@getById');
        Route::post('sms',           'SmsController@create');
        Route::post('sms/mass',      'SmsController@createFew');
        Route::put('sms/{id}',       'SmsController@update');
        Route::delete('sms/{id}',    'SmsController@delete');

        Route::get('sms_provider',            'SmsProvidersController@getList');
        Route::get('sms_provider/{id}',       'SmsProvidersController@getById');
        Route::post('sms_provider',           'SmsProvidersController@create');
        Route::put('sms_provider/{id}',       'SmsProvidersController@update');
        Route::delete('sms_provider/{id}',    'SmsProvidersController@delete');

        Route::get('sms_rules',            'SmsRulesController@getList');
        Route::get('sms_rules/{id}',       'SmsRulesController@getById');
        Route::post('sms_rules',           'SmsRulesController@create');
        Route::put('sms_rules/{id}',       'SmsRulesController@update');
        Route::delete('sms_rules/{id}',    'SmsRulesController@delete');

        
        Route::get('organizations',                             'OrganizationsController@dxOrganizations');
        Route::get('organizations/{id}',                        'OrganizationsController@getById');
        Route::get('organizations/{id}/list',                   'OrganizationsController@getOrganizations');
        Route::put('organizations/{id}',                        'OrganizationsController@update');
        Route::get('organizations/{id}/children',               'OrganizationsController@getList');
        Route::get('organizations/{id}/company',                'OrganizationsController@getCompanyList');
        Route::post('organizations/{id}/children',              'OrganizationsController@create');
        Route::get('organizations/{id}/by_role',                'OrganizationsController@getListByRole');
        Route::get('organizations/{id}/by_organization',        'OrganizationsController@getByOrganization');
        Route::post('organizations/{id}/for_tree',               'OrganizationsController@getOrgForTree');
        Route::post('organizations/{id}/attach_role',            'OrganizationsController@attachRole');
        Route::post('organizations/{id}/detach_role',            'OrganizationsController@detachRole');
        Route::get('organizations/get/api_key',                'OrganizationsController@getApiKey');

        Route::get('organizations/{id}/permissions',            'PermissionsController@getByOrganization');
//        Route::put('organizations/{id}/permissions',            'PermissionsController@updateByOrganization');
        Route::post('organizations/{id}/permissions',           'PermissionsController@createByOrganization');
        Route::get('organizations/{id}/permissions/shared',     'PermissionsController@getShared');
        Route::get('permissions/{id}',                          'PermissionsController@getById');
        Route::put('permissions/{id}',                          'PermissionsController@update');

        Route::get('dx_project_category',         'ProjectCategoryController@dxProjectCategory');
        Route::get('project_category',            'ProjectCategoryController@getList');
        Route::get('project_category/suggest',    'ProjectCategoryController@getSuggest');
        Route::get('project_category/{id}',       'ProjectCategoryController@getById');
        Route::post('project_category',           'ProjectCategoryController@create');
        Route::put('project_category/{id}',       'ProjectCategoryController@update');
        Route::delete('project_category/{id}',    'ProjectCategoryController@delete');
        
        Route::get('project_category_kc',         'ProjectCategoryKcController@get');

        Route::get('traffics',            'TrafficsController@getList');
        Route::get('traffics/{id}',       'TrafficsController@getById');
        Route::post('traffics',           'TrafficsController@create');
        Route::put('traffics/{id}',       'TrafficsController@update');
        Route::delete('traffics/{id}',    'TrafficsController@delete');

        Route::get('dx_project_page',         'ProjectPageController@dxProjectPage');
        Route::get('project_page',            'ProjectPageController@getList');
        Route::get('project_page/{id}',       'ProjectPageController@getByProjectId');
        Route::post('project_page',           'ProjectPageController@create');
        Route::put('project_page/{id}',       'ProjectPageController@update');
        Route::delete('project_page/{id}',    'ProjectPageController@delete');

        Route::get('dx_product_category',         'ProductCategoryController@dxProductCategory');
        Route::get('product_category',            'ProductCategoryController@getList');
        Route::get('product_category/{id}',       'ProductCategoryController@getById');
        Route::post('product_category',           'ProductCategoryController@create');
        Route::put('product_category/{id}',       'ProductCategoryController@update');
        Route::delete('product_category/{id}',    'ProductCategoryController@delete');

        Route::get('project_page_phone',            'ProjectPagePhoneController@getList');
        Route::get('project_page_phone/{id}',       'ProjectPagePhoneController@getByPageId');
        Route::post('project_page_phone',           'ProjectPagePhoneController@create');
        Route::put('project_page_phone/{id}',       'ProjectPagePhoneController@update');
        Route::delete('project_page_phone/{id}',    'ProjectPagePhoneController@delete');

        Route::get('project_gasket',            'ProjectGasketController@getList');
        Route::get('project_gasket/{id}',       'ProjectGasketController@getByProjectId');
        Route::post('project_gasket',           'ProjectGasketController@create');
        Route::put('project_gasket/{id}',       'ProjectGasketController@update');
        Route::delete('project_gasket/{id}',    'ProjectGasketController@delete');


        Route::get('call_center',            'CallCenterController@getList');
        Route::get('call_center/{id}',       'CallCenterController@getById');
        Route::post('call_center',           'CallCenterController@create');
        Route::put('call_center/{id}',       'CallCenterController@update');
        Route::delete('call_center/{id}',    'CallCenterController@delete');


        Route::get('currency',            'CurrencyController@getList');
        Route::get('currency/{id}',       'CurrencyController@getById');
        Route::post('currency',           'CurrencyController@create');
        Route::put('currency/{id}',       'CurrencyController@update');
        Route::delete('currency/{id}',    'CurrencyController@delete');

        Route::get('project_goal',            'ProjectGoalController@getList');
        Route::get('project_goal/{id}',       'ProjectGoalController@getByProjectId');
        Route::post('project_goal',           'ProjectGoalController@create');
        Route::put('project_goal/{id}',       'ProjectGoalController@update');
        Route::delete('project_goal/{id}',    'ProjectGoalController@delete');

        Route::get('project_goal_script',            'ProjectGoalScriptController@getList');
        Route::get('project_goal_script/{id}',       'ProjectGoalScriptController@getByProjectGoalId');
        Route::post('project_goal_script',           'ProjectGoalScriptController@create');
        Route::post('project_goal_script/{id}',       'ProjectGoalScriptController@update');
        //Route::delete('project_script/{id}',    'ProjectGoalController@delete');

        Route::get('delivery_type_projects/{id}',       'DeliveryTypeProjectsController@getByDeliveryTypeId');
        Route::post('delivery_type_projects/{id}',           'DeliveryTypeProjectsController@create');
        Route::delete('delivery_type_projects/{id}/{delivery_id}/{geo_id}',       'DeliveryTypeProjectsController@update');

        Route::get('entities',            'EntitiesController@getList');
        Route::get('entities/{id}',       'EntitiesController@getById');
        Route::post('entities',           'EntitiesController@create');
        Route::put('entities/{id}',       'EntitiesController@update');
        Route::delete('entities/{id}',    'EntitiesController@delete');

        Route::get('entity_params',                       'EntityParamsController@getList');
        Route::get('entity_params/{id}',                  'EntityParamsController@getById');
        Route::get('entity_params/{id}/children',         'EntityParamsController@getAllByParentId');
        Route::get('entity_params/{id}/by_entity',        'EntityParamsController@getAllByEntityId');
        Route::post('entity_params/by_entity_role',       'EntityParamsController@getByEntityAndRole');
        Route::post('entity_params/by_role_permitted',    'EntityParamsController@getByRolePermitted');
        Route::post('entity_params',                      'EntityParamsController@create');
        Route::put('entity_params/{id}',                  'EntityParamsController@update');
        Route::delete('entity_params/{id}',               'EntityParamsController@delete');

        Route::get('role_groups',            'RoleGroupsController@getList');
        Route::get('role_groups/{id}',       'RoleGroupsController@getById');
        Route::post('role_groups',           'RoleGroupsController@create');
        Route::put('role_groups/{id}',       'RoleGroupsController@update');
        Route::delete('role_groups/{id}',    'RoleGroupsController@delete');

        Route::get('roles',                                 'RolesController@getList');
        Route::get('roles/{id}',                            'RolesController@getById');
        Route::get('roles/{id}/by_group',                   'RolesController@getByGroupId');
        Route::get('roles/{id}/organizations_projects',     'RolesController@getOrganizationsProjects');
        Route::get('roles/{id}/groups_by_access',           'RolesController@getGroupsByAccess');
        Route::get('roles/{id}/geos',                       'RolesController@getGeos');
        Route::post('roles/{id}/by_access',                 'RolesController@getByAccess');
        Route::post('roles',                                'RolesController@create');
        Route::post('roles/attach_params',                  'RolesController@attachParams');
        Route::post('roles/attach_status',                  'RolesController@attachStatus');
        Route::post('roles/attach_all_child_status',        'RolesController@attachAllChildStatus');
        Route::post('roles/attach_organizations_projects',  'RolesController@attachOrganizationsProjects');
        Route::post('roles/attachGeos',                      'RolesController@attachGeos');
        Route::put('roles/{id}',                            'RolesController@update');
        Route::delete('roles/{id}',                         'RolesController@delete');
        Route::post('roles/copy_settings',                  'RolesController@copySettings');
        
        /**************************************
         *                 Ats                *
         **************************************/
        /**
         * Создает новую запись в таблице АТС
         * @param string ip Айпи сервера (должен быть уникальным)
         * @param string name Имя сервера (должно быть уникальным)
         * @param string key АПИ ключ (md5) (если не указать, создаётся сам)
         * @param string description Комментарий или описание
         * @param boolean is_work Включена ли АТС
         * @param boolean is_default АТС по дефолту
         * @example {
         *              "ip":"127.0.0.1",
         *              "name":"localhost",
         *              "key":"1BC29B36F623BA82AAF6724FD3B16718",
         *              "description":"Example ATS",
         *              "is_work":true,
         *              "is_default":false
         *          }
         */
        Route::post('ats', 'AtsController@store');
        
        // Возвращает список всех АТС
        Route::get('ats', 'AtsController@index');
        
        // Возвращает АТС по ID
        Route::get('ats/{id}', 'AtsController@show');
        
        // Редактирует АТС по ID
        // Параметры те же, что и у Route::post('ats', 'AtsController@store');
        Route::put('ats/{id}', 'AtsController@update');
        
        // Удаляет АТС по ID
        Route::delete('ats/{id}', 'AtsController@destroy');
        
        Route::get('ats/{id}/reconfigure', 'AtsController@reconfigure');
        
        /*******************************
         *          AtsGroup           *
         *******************************/
        /**
         * Создает новую запись в таблице групп АТС
         * @param string name Имя группы (должно быть уникальным)
         * @param string description Комментарий или описание
         * @param boolean is_work Включена ли группа
         * @param integer ats_id ID связанной АТС
         * @param integer organization_id ID организации, к которой привяжется создаваемая группа
         *                                Если оставить пустым, то привязка к организации пользователя
         * @example {
         *              "name":"Группа АТС Старт",
         *              "description":"Стартовый тариф, ограниченные возможности",
         *              "is_work":true,
         *              "ats_id":5,
         *              "organization_id":125
         *          }
         */
        Route::post('ats_groups', 'AtsGroupController@store');

        // Возвращает группу АТС по ID
        // {force} Без проверки доступа !!!
        Route::get('ats_groups/{id}/{force?}', 'AtsGroupController@show')->where(['id' => '[0-9]+', 'force' => 'f']);
        
        // Возвращает список всех доступных пользователю групп АТС
        // {force} Без проверки доступа !!!
        Route::get('ats_groups/{force?}', 'AtsGroupController@index')->where(['force' => 'f']);
        
        // Возвращает список всех доступных пользователю групп АТС
        // {force} Без проверки доступа !!!
        Route::get('ats_groups/by_role/{role_id}', 'AtsGroupController@listByRole');

        // Редактирует группу АТС по ID
        // Параметры те же, что и у Route::post('ats_groups', 'AtsGroupController@store');
        Route::put('ats_groups/{id}', 'AtsGroupController@update');

        // Удаляет группу АТС по ID
        Route::delete('ats_groups/{id}', 'AtsGroupController@destroy');
        
        // Привязывает группу к компании/компаниям
        Route::post('ats_groups/{id}/attach_organizations', 'AtsGroupController@attachOrganizations');
        
        // Возвращает список AtsGroup принадлежащих организации
        // Если ID не указан, то используется организация пользователя
        Route::get('ats_groups/by_organization/{id?}', 'AtsGroupController@getListByOrganization');
        
        // Возвращает организации, которые связаны с группой
        Route::get('ats_groups/{id}/get_organizations', 'AtsGroupController@getOrganizations');
        
        /*******************************
         *             SIP             *
         *******************************/
         /**
          * Создает новую запись в таблице транков
          * @param string   host            Домен или IP-адрес подключения 
          * @param integer  port            Порт подключения - шестизначные значения
          * @param string   passwd          Пароль в открытом виде
          * @param string   login           Логин
          * @param integer  max_channels    Максимальное количество каналов
          * @param string   template        Шаблон "SIP", "VOIP", "LOCAL" или "GSM"
          * @param string   connect_type    Тип подключения "SIP" или "IP"
          * @param integer  ats_group_id    ID группы АТС, которой принадлежит транк
          * @param boolean  is_work         Включен ли транк
          * @example {
          *              "host":"https://axsmak.kz/sip",
          *              "port":5060,
          *              "passwd":"123456Qq@",
          *              "login":"axsmak",
          *              "max_channels":16,
          *              "template":"GSM",
          *              "connect_type":"SIP",
          *              "ats_group_id":3,
          *              "is_work":true
          *          }
          */
         Route::post('sips', 'SipController@store');
         
         //Возвращает список всех доступных пользователю транков
         Route::get('sips', 'SipController@index');
         
         // Возвращает транк по ID, если есть доступ
         Route::get('sips/{id}', 'SipController@show')->where(['id' => '[0-9]+']);
         
         // Редактирует транк по ID, если есть доступ
         Route::put('sips/{id}', 'SipController@update')->where(['id' => '[0-9]+']);
         
         // Удаляет транк по ID, если есть доступ
         Route::delete('sips/{id}', 'SipController@destroy')->where(['id' => '[0-9]+']);
         
         // Возвращает каллер айди транка
         Route::get('sips/{id}/caller_ids', 'SipController@callerIds')->where(['id' => '[0-9]+']);
         
         // Возвращает каллер айди транков, которые доступны пользователю
         Route::get('sips/caller_ids', 'SipController@allCallerIds');
         
         /******************************************
          *              SipCallerIds              *
          ******************************************/
         /**
          * Создаёт новый каллер айди
          * @param integer  sip_id          Если принадлежит транку, то айди существуещего транка,
          *                                 иначе null
          * @param integer  ats_user_id     Если принадлежит пользователю АТС, то айди существующего
          *                                 пользователя АТС, иначе null
          * @param string   caller_id       Каллер айди
          * @param integer  ats_queue_id    null или айди существующей очереди, если привязан к очереди
          * @example    {
          *                 "sip_id": 1,
          *                 "caller_id": "id9798797523",
          *                 "ats_queue_id": 3
          *             }
          */

         Route::post('caller_ids', 'SipCallerIdController@store');

         Route::put('caller_ids/{id}', 'SipCallerIdController@update')->where(['id' => '[0-9]+']);
         Route::get('caller_ids', 'SipCallerIdController@index');
         
         // Возвращает доступные каллер айди (номера телефонов транков и незвисимых пользователей) 
         // для входящих очередей (type 'in')
         Route::get('caller_ids/get_for_in', 'SipCallerIdController@getForIn');
         
         // Возвращает доступные каллер айди операторов (агентов)
         Route::get('caller_ids/get_operators/{queue_id}', 'SipCallerIdController@getOperators');
         
         // Возвращает хххх cаller_id, который не привязан ни к какому пользователю, 
         // либо который ещё не существет в базе
         Route::get('caller_ids/free_private', 'SipCallerIdController@getFreePrivate');


        /*******************************
         *        OrdersDialSteps      *
         *******************************/
         /**
          * Создает шаг перезвона заказа 
          * @param integer  queue_id            Айди очереди в которой производился вызов
          * @param integer  order_id            Айди заказа
          * @param integer  dial_step           Шаг перезвона
          * @param integer  dial_time           Время когда должен совершиться звонок
          * @example {
          *              "queue_id": 1,
          *              "order_id": 1412433,
          *              "dial_step": 1,
          *              "dial_time": "2018-04-19 11:44:09"
          *          }
          */

         // Создает новый или обновляет по уникальности полей queue_id и order_id
         Route::post('orders_dial_step/create_or_update', 'OrdersDialStepsController@createOrUpdate');

         /******************************************
          *               Providers                *
          ******************************************/
         /**
          * Создаёт новую запись в таблице провайдеров
          * @param string   name    Наименование провайдера
          * @param string   comment Комментарий или описание
          * @param string   img     всегда null
          * @param file     logo    Файл с логотипом провайдера (jpeg, png, tiff, gif, jp2, bmp)          *
          */
         Route::post('providers', 'ProviderController@store');
         
         /**
          * Редактирует провайдера по ID
          * Запрос отправляется методом POST!!!
          * 
          * @param string   name    Наименование провайдера
          * @param string   comment Комментарий или описание
          * @param string   img     Имя файла под которым логотип провайдера будет сохранён Например: /img/providers/beeline_logo.jpg)
          * @param file     logo    Файл с логотипом провайдера (jpeg, png, gif)
          * @param string   _method Всегда равен PUT
          */
         Route::put('providers/{id}', 'ProviderController@update');
         
         Route::get('providers/{id}', 'ProviderController@show');
         Route::delete('providers/{id}', 'ProviderController@destroy');
         Route::get('providers', 'ProviderController@index');

         /*********************************************
          *                 AtsUsers                  *
          *********************************************/
          /**
           * Создаёт новую запись в таблице пользователей АТС
           * @param integer  port           Порт подключения. По умолчанию (если опустить) равен 5060
           * @param string   passwd         Пароль в открытом виде
           * @param string   login          Логин
           * @param integer  max_channels   Максимальное количество каналов
           * @param string   type           Тип аккаунта "independent" или "privat"
           * @param integer  ats_group_id   ID группы АТС
           * @param integer  user_id        ID пользователя системы
           * @param boolean  is_work        Включен ли пользователь
           * @example {
           *              "port":5060,
           *              "passwd":"123456Qq@",
           *              "login":"axsmak",
           *              "max_channels":16,
           *              "type":"privat",
           *              "ats_group_id":3,
           *              "user_id":13732,
           *              "is_work":true
           *          }
           */
          Route::post('ats_users', 'AtsUserController@store');
          
          // Редактирует пользователя АТС по ID
          Route::put('ats_users/{id}', 'AtsUserController@update');
          
          // Возвращает список пользователей АТС доступных пользователю
          Route::get('ats_users', 'AtsUserController@index');
          
          // Возвращает пользователя АТС по ID
          Route::get('ats_users/{id}', 'AtsUserController@show')->where(['id' => '[0-9]+']);
          
          // Удаляет пользователя АТС по ID
          Route::delete('ats_users/{id}', 'AtsUserController@destroy');

          Route::get('ats_users/{id}/caller_ids', 'AtsUserController@callerIds')->where(['id' => '[0-9]+']);
          
          Route::get('ats_users/is_online', 'AtsUserController@isOnline');
          Route::get('ats_users/in_calls_switch/{val}', 'AtsUserController@inCallsSwitch');
          
          // Возвращает хххх cаller id, который не привязан ни к какому пользователю, 
          // либо который ещё не существет в базе
          Route::get('caller_ids/free_private', 'SipCallerIdController@getFreePrivate');
         
          Route::resource('out_routes', 'OutRouteController', ['parameters' => 
             ['out_route'=>'id']
          ]);

        /****************************************
         *              AtsQueues               *
         ****************************************/
        /**
         * Связывает каллер айди с очередью по ID очереди
         * @param Array caller_ids  Массив с ID каллер айди, которые требуется связать с очередью
         * @param Array sorting     Массив с позициями сортировки каллер айди. 
         *                          Если не указать массив sorting, то всем каллер айди 
         *                          будет назначена позиция сортировки 1
         *                          Если массив меньше массива caller_ids, то оставшимся каллер айди 
         *                          будет назначена позиция сортировки 1
         * @example {
         *              "caller_ids": [2,3,17,13,21],
         *              "sorting": [1,2,5]
         *          }
         */
        Route::post('ats_queues/{id}/attach_operators', 'AtsQueueController@attachOperators');
        Route::post('ats_queues/{id}/attach_trunks', 'AtsQueueController@attachTrunks');
        Route::get('ats_queues/by_company/{id}', 'AtsQueueController@getByCompany');
        Route::post('ats_queues/{id}/attach_companies', 'AtsQueueController@attachCompanies');
        Route::get('ats_queues/{id}/reconfigure', 'AtsQueueController@reconfigure');
        Route::put('ats_queues', 'AtsQueueController@store');
        Route::get('ats_queues/reindex', function () {
            $exitCode = Artisan::call('search:create_index', [
                'index' => 'ats_queues', '--reindex' => true
            ]);
            return response()->json(['data' => $exitCode]);
        });
        
        /**
         * Создание/редактирование очереди
         * 
         * @param string  type         Тип очереди 'in' или 'auto'
         * @param string  name         Имя очереди
         * @param string  comment      Описание или комментарий
         * @param string  steps1       Шаги прозвона json-строка
         * @param string  steps2       Шаги прозвона json-строка
         * @param string  off_time1    Время начала перерыва в формате 'HH:MM:SS'
         * @param string  off_time2    Время начала перерыва в формате 'HH:MM:SS'
         * @param integer how_call     Тип авто-набора см. задачу crm2-136 в ютреке
         * @param string  strategy     Стратегия набора. По умолчанию 'random'. Cм. задачу crm2-136 в ютреке
         * @param boolean check_wbt    Вебмастер или партнёрка
         * @param integer unload_id    Айди связанной выгрузки
         * @param integer ats_group_id Айди связанной группы АТС
         * @example {
         *              "type":"auto",
         *              "name":"Автодозвон 1",
         *              "comment":"",
         *              "steps1":"{\"1\":{\"step\":1,\"time\":0,\"weigth\":90}}",
         *              "steps2":"{\"1\":{\"step\":1,\"time\":0,\"weigth\":50}}",
         *              "off_time1":"21:30:00",
         *              "off_time2":"08:45:00",
         *              "how_call":2,
         *              "check_wbt":true,
         *              "unload_id":5,
         *              "ats_group_id":1
         * }
         */
        Route::resource('ats_queues', 'AtsQueueController', ['parameters' => 
            ['ats_queue'=>'id']
        ]);
        
        Route::get('ats_monitoring/get_oper_states', 'AtsMonitoringController@getOperStates');
        Route::get('ats_monitoring/get_current_calls', 'AtsMonitoringController@getCurrentCalls');
        Route::get('ats_monitoring/get_dial_coeff', 'AtsMonitoringController@getDialCoeff');
        Route::get('ats_monitoring/mini_analytics', 'AtsMonitoringController@miniAnalytics');
        Route::get('ats_monitoring/get_lags/{queue_id?}', 'AtsMonitoringController@getLags');
        Route::put('ats_monitoring/move_up_orders', 'AtsMonitoringController@moveUp');
        Route::put('ats_monitoring/move_down_orders', 'AtsMonitoringController@moveDown');

        Route::group(['prefix' => 'optovichok', 'namespace' => 'Optovichok'], function(){
            Route::get('clients/', 'ClientsController@getList');
            Route::get('clients/{id}', 'ClientsController@getById');
            Route::post('clients', 'ClientsController@create');
            Route::put('clients/{id}', 'ClientsController@update');
            Route::delete('clients/{id}', 'ClientsController@delete');
        });

    });
});

