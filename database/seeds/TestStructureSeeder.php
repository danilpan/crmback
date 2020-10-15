<?php

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;



class TestStructureSeeder extends Seeder
{
    protected $facker;

    protected $dedaultPass;

    public function run()
    {
        $this->dedaultPass  = Hash::make('111111');
        $this->facker       = $faker = Faker\Factory::create('ru_RU');


        $data   = [
            [
                'organization'   => [
                    'title'         => 'Crmka.pro',
                ],
                'users' => [
                    [
                        'mail'  => 'admin',
                        'min'   => 1,
                        'max'   => 1
                    ]
                ],
                'children' => [
                    [
                        'organization'  => [
                            'title'         => 'Глобальный менеджмент',
                        ],
                        'users' => [
                            [
                                'mail'  => 'management',
                                'max'   => 5,
                                'min'   => 2
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title'         => 'Отдел разработки',
                        ],
                        'users' => [
                            [
                                'mail'  => 'development',
                                'max'   => 5,
                                'min'   => 2
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title'         => 'Саппорт СРМ',
                        ],
                        'users' => [
                            [
                                'mail'  => 'support',
                                'max'   => 5,
                                'min'   => 2
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title'         => 'Ротарь',
                        ],
                        'users' => [
                            [
                                'mail'  => 'rotar',
                                'max'   => 20,
                                'min'   => 7
                            ]
                        ],
                        'children'  => [
                            [
                                'organization'  => [
                                    'title' => 'КЦ Жетысу'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'kc_zhetysu',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ],
                                'children'  => [
                                    [
                                        'organization'  => [
                                            'title' => 'Тим лидеры'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rotar_timlidery',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Операторы'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rotar_operatory',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Старшие операторы'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rotar_starshie_operatory',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Логисты'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rotar_logisty',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title' => 'МЛ CRM'
                        ],
                        'users' => [
                            [
                                'mail'      => 'ml_crm',
                                'max'       => 5,
                                'min'       => 2
                            ]
                        ],
                        'children'  => [
                            [
                                'organization'  => [
                                    'title' => 'Тим лидеры'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'ml_timlidery',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Логисты'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'ml_logisty',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Фрод'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'ml_frod',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Аквабронь'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'akvabron',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title' => 'Rekl pro kz hisamutdinov n'
                        ],
                        'users' => [
                            [
                                'mail'      => 'rekl_pro_kz',
                                'max'       => 7,
                                'min'       => 3
                            ]
                        ],
                        'children' => [
                            [
                                'organization'  => [
                                    'title' => 'Менеджеры по продажам'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_managers_sale',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ],
                                'children' => [
                                    [
                                        'organization'  => [
                                            'title' => 'КЦ Жетысу'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_kz_kc_zhetysu',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ],
                                        'children' => [
                                            [
                                                'organization'  => [
                                                    'title' => 'Тим лидеры'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_kz_kc_zhetysu_timlidery',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_kz_kc_zhetysu_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Старшие операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_kz_kc_zhetysu_older_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'КЦ Текели'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_kz_kc_tekeli',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ],
                                        'children' => [
                                            [
                                                'organization'  => [
                                                    'title' => 'Тим лидеры'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_kz_kc_tekeli_timlidery',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_kz_kc_tekeli_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Старшие операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_kz_kc_tekeli_older_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ]

                            ],
                            [
                                'organization'  => [
                                    'title' => 'Бухгалтерия'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_accounting',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Склад'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_stock',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Маркетинг'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_marketing',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Менеджмент'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_management',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Аудиторы'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_auditors',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Скриптологи'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_scriptologi',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Логистика'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_kz_logistics',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ],
                                'children' => [
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты KZ'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_kz_logistics_kz',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты KG'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_kz_logistics_kg',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты UZ'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_kz_logistics_uz',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                ]
                            ],
                        ]

                    ],
                    [
                        'organization'  => [
                            'title' => 'Rekl pro ru belkin g'
                        ],
                        'users' => [
                            [
                                'mail'      => 'rekl_pro_ru',
                                'max'       => 5,
                                'min'       => 2
                            ]
                        ],
                        'children' => [
                            [
                                'organization'  => [
                                    'title' => 'Менеджер по прадажам нижний новгород'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_menedzher_nizh_nov',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Бухгалтерия'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_accounting',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Склад'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_stock',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Маркетинг'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_marketing',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Аудиторы'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_auditors',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Менеджмент'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_management',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Логистика'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_logistics',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ],
                                'children' => [
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты AZ'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_logistic_az',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты AM'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_logistic_am',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты RU'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_logistic_ru',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты RB'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_logistic_by',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Менеджеры по продажам'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_managers',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ],
                                'children' => [
                                    [
                                        'organization'  => [
                                            'title' => 'КЦ Полименторы'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_kc_polimentory',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ],
                                        'children' => [
                                            [
                                                'organization'  => [
                                                    'title' => 'Тим лидеры'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'kc_polimentory_timlidery',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'kc_polimentory_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'КЦ Жетысу'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_kc_zhetysu',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ],
                                        'children' => [
                                            [
                                                'organization'  => [
                                                    'title' => 'Тим лидеры'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_zhetysu_timlidery',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Старшие операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_zhetysu_older_oper',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_zhetysu_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'КЦ Уштобе'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_kc_ushtobe',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ],
                                        'children' => [
                                            [
                                                'organization'  => [
                                                    'title' => 'Тим лидеры'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_ushtobe_timlidery',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Старшие операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_ushtobe_older_oper',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_ushtobe_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'КЦ AZ'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_kc_az',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ],
                                        'children' => [
                                            [
                                                'organization'  => [
                                                    'title' => 'Тим лидеры'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_az_timlidery',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_az_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'КЦ AZ'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_kc_autsors',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ],
                                        'children' => [
                                            [
                                                'organization'  => [
                                                    'title' => 'Тим лидеры'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_autsors_timlidery',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                            [
                                                'organization'  => [
                                                    'title' => 'Операторы'
                                                ],
                                                'users' => [
                                                    [
                                                        'mail'      => 'rekl_pro_ru_kc_autsors_operatory',
                                                        'max'       => 5,
                                                        'min'       => 2
                                                    ]
                                                ]
                                            ],
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => '1С Разработчики'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_1c_dev',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Купальники'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_swimsuits',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ],
                                'children' => [
                                    [
                                        'organization'  => [
                                            'title' => 'Тим лидеры'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_swimsuits_tim',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Операторы'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_swimsuits_oper',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_swimsuits_log',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ],
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Скриптологи'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_scriptolog',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Оптовик'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'rekl_pro_ru_wholesaler',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ],
                                'children' => [
                                    [
                                        'organization'  => [
                                            'title' => 'Логисты'
                                        ],
                                        'users' => [
                                            [
                                                'mail'      => 'rekl_pro_ru_wholesaler_log',
                                                'max'       => 5,
                                                'min'       => 2
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]

                    ],
                    [
                        'organization'  => [
                            'title' => 'HR Менеджеры KZ'
                        ],
                        'users' => [
                            [
                                'mail'      => 'hr_kz',
                                'max'       => 5,
                                'min'       => 2
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title' => 'HR Менеджеры RU'
                        ],
                        'users' => [
                            [
                                'mail'      => 'hr_ru',
                                'max'       => 5,
                                'min'       => 2
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title' => 'World wide'
                        ],
                        'users' => [
                            [
                                'mail'      => 'worldwide',
                                'max'       => 5,
                                'min'       => 2
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title' => 'Worldwide - reklpro'
                        ],
                        'users' => [
                            [
                                'mail'      => 'worldwide_reklpro',
                                'max'       => 5,
                                'min'       => 2
                            ]
                        ]
                    ],
                    [
                        'organization'  => [
                            'title' => 'Чукилев'
                        ],
                        'users' => [
                            [
                                'mail'      => 'chukilev',
                                'max'       => 5,
                                'min'       => 2
                            ]
                        ],
                        'children' => [
                            [
                                'organization'  => [
                                    'title' => 'КЦ'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'chukilev_kc',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ],
                            [
                                'organization'  => [
                                    'title' => 'Аудиторы'
                                ],
                                'users' => [
                                    [
                                        'mail'      => 'chukilev_auditors',
                                        'max'       => 5,
                                        'min'       => 2
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $user   = User::create([
            'login'         => 'admin',
            'first_name'    => 'Администратор',
            'mail'          => 'admin@admin.com',
            'password'      => $this->dedaultPass
        ]);

        for($i = 0; $i <5; $i++) {
            $this->createOrganizations($data, $i + 1);
        }
    }

    protected function createOrganizations(array $data, $count, $parent = null)
    {
        foreach ($data as $d) {
            $organization   = $this->createOrganization($d, $count, $parent);
            $users          = ($d['users']) ? $this->createUsers($d['users'], $count, $organization) : [];

            if(!empty($d['children'])) {
                $this->createOrganizations($d['children'], $count, $organization);
            }
        }
    }

    protected function createUsers($data, $count, $organization)
    {
        foreach ($data as $type) {
            $companyPref    = 'company_' . $count;
            $companyPost    = '@' . $companyPref . '.com';


            if($type['min'] == 1 && $type['max'] == 1) {
                $login  = $companyPref . '_' . $type['mail'];
                $mail   = $login . $companyPost;

                $user   = User::create([
                    'login'             => $login,
                    'password'          => $this->dedaultPass,
                    'mail'              => $mail,
                    'organization_id'   => $organization->id,
                    'first_name'        => $this->facker->firstName,
                    'last_name'         => $this->facker->lastName,
                    'middle_name'       => $this->facker->middleName,
                    'phone'             => $this->facker->e164PhoneNumber
                ]);
            }
            else {
                $total  = rand($type['min'], $type['max']);
                for($i=0; $i<$total; $i++) {
                    $login  = $companyPref . '_' . $type['mail'] . '_' . ($i + 1);
                    $mail   = $type['mail'] . '_' . ($i + 1) . $companyPost;
                    $user   = User::create([
                        'login'             => $login,
                        'password'          => $this->dedaultPass,
                        'mail'              => $mail,
                        'organization_id'   => $organization->id,
                        'first_name'        => $this->facker->firstName,
                        'last_name'         => $this->facker->lastName,
                        'middle_name'       => $this->facker->middleName,
                        'phone'             => $this->facker->e164PhoneNumber
                    ]);
                }
            }
        }
    }

    protected function createOrganization($data, $count, $parent)
    {
        $title  = $data['organization']['title'];
        if(!$parent) {
            $title  .= ' ' . $count;
        }

        $organization    = Organization::create([
            'title'         => $title,
            'permission_id' => $data['organization']['permission_id'] ?? null
        ]);

        if($parent) {
            $organization->makeChildOf($parent);
        }

        return $organization;
    }
}
