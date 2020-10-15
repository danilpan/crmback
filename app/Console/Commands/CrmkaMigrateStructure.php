<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use DB;

class CrmkaMigrateStructure extends Command
{

    protected $signature        = 'crmka:migrate:structure';


    protected $description      = 'Command description';


    protected $newStructureIds  = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::transaction(function () {
            $org = Organization::create(['title' => 'BRAND NEW CRMKA.pro']);

            $map = [
                '26' => $org->id,
                '27' => $org->id,

                '2' => $org->id,
                 '124' => $org->id,
                '139' => $org->id,
                '151' => $org->id,
                '159' => $org->id,
                '161' => $org->id,

                '47' => $org->id,
                //'48' => 47,
               // '53' => 47,
                //'137' => 47,

                '66' => $org->id,
                '68' => 66,
                '69' => 66,
                '70' => 66,
                '71' => 66,
                '85' => 66,
                '88' => 66,
                '72' => 88,
                '109' => 88,

                '67' => $org->id,
                '74' => 67,
                '75' => 67,
                '73' => 67,
                '77' => 67,
                '154' => 67,
                '82' => 67,
               // '118' => 67,
               // '129' => 67,
               // '117' => 67,
               // '152' => 67,
                '155' => 67,
                '149' => 67,
                '150' => 149,

            ];

            $new = [
                'title'=> 'BRAND NEW CRMKA.pro',
                'id'=> $org->id,
                'childs'=> [
                    [
                        'title'=> 'Саппорт СРМ',
                        'id'=> null,
                    ],
                    [
                        'title'=>'ХИС СРМ',
                        'id'=> 66,
                        'childs'=>[
                            [
                                'title'=> 'Аудиторы',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Скриптологи',
                                'id'=> null,
                            ]
                        ]
                    ],
                    [
                        'title'=> 'Аудиторы',
                        'id'=> 161,
                        'childs'=>[
                            [
                                'title'=> 'КЦ',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Аудиторы',
                                'id'=> null,
                            ]
                        ]
                    ],
                    [
                        'title'=> 'Ротарь СРМ',
                        'id'=> '2',
                        'childs'=>[
                            [
                                'title'=> 'Логисты',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Операторы',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Старшие операторы',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Тим лидеры',
                                'id'=> null,
                            ],
                        ]
                    ],
                    [
                        'title'=> 'Менеджеры по продажам',
                        'id'=> '68',
                        'childs'=>[
                            [
                                'title'=> 'КЦ Жетысу',
                                'id'=> null,
                                'childs'=>[
                                    [
                                        'title'=> 'Тим лидеры',
                                        'id'=> null,
                                    ],
                                    [
                                        'title'=> 'Операторы',
                                        'id'=> null,
                                    ],
                                    [
                                        'title'=> 'Старшие менеджеры по продажам',
                                        'id'=> null,
                                    ],
                                ]
                            ],
                            [
                                'title'=> 'КЦ Текели',
                                'id'=> null,
                                'childs'=>[
                                    [
                                        'title'=> 'Тим лидеры',
                                        'id'=> null,
                                    ],
                                    [
                                        'title'=> 'Операторы',
                                        'id'=> null,
                                    ],
                                    [
                                        'title'=> 'Старшие менеджеры по продажам',
                                        'id'=> null,
                                    ],
                                ]
                            ],
                        ]
                    ],
                    [
                        'title'=> 'МЛ СРМ',
                        'id'=> '47',
                        'childs'=>[
                            [
                                'title'=> 'Тим лидеры',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Логисты',
                                'id'=> 48,
                            ],
                            [
                                'title'=> 'Фрод',
                                'id'=> 53,
                            ],
                            [
                                'title'=> 'Аквабронь',
                                'id'=> 137,
                            ],
                        ]
                    ],
                    [
                        'title'=> 'Ротарь СРМ',
                        'id'=> '47',
                        'childs'=>[
                            [
                                'title'=> 'Логисты',
                                'id'=> null,
                            ],
                        ]
                    ],
                    [
                        'title'=> 'Логистика',
                        'id'=> '88',
                        'childs'=>[
                            [
                                'title'=> 'Логисты UZ',
                                'id'=> null,
                            ],
                        ]
                    ],
                    [
                        'title'=> 'Логистика',
                        'id'=> '82',
                        'childs'=>[
                            [
                                'title'=> 'Логисты AZ',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Логисты AM',
                                'id'=> null,
                            ],
                            [
                                'title'=> 'Логисты BY',
                                'id'=> null,
                            ],
                        ]
                    ],
                    [
                        'title'=> 'Белкин СРМ',
                        'id'=> '67',
                        'childs'=>[
                            [
                                'title'=> 'Менеджеры по продажам',
                                'id'=> null,
                                'childs'=>[
                                    [
                                        'title'=> 'КЦ Полименторы',
                                        'id'=> null,
                                        'childs'=>[
                                            [
                                                'title'=> 'Тим лидеры',
                                                'id'=> 111,
                                            ],
                                            [
                                                'title'=> 'Операторы',
                                                'id'=> 110,
                                            ]
                                        ]
                                    ],
                                    [
                                        'title'=> 'КЦ Жетысу',
                                        'id'=> null,
                                        'childs'=>[
                                            [
                                                'title'=> 'Тим лидеры',
                                                'id'=> 118,
                                            ],
                                            [
                                                'title'=> 'Операторы',
                                                'id'=> 117,
                                            ],
                                            [
                                                'title'=> 'Старшие менеджеры по продажам',
                                                'id'=> 129,
                                            ]
                                        ]
                                    ],
                                    [
                                        'title'=> 'КЦ Уштобе',
                                        'id'=> null,
                                        'childs'=>[
                                            [
                                                'title'=> 'Тим лидеры',
                                                'id'=> null,
                                            ],
                                            [
                                                'title'=> 'Операторы',
                                                'id'=> 152,
                                            ],
                                            [
                                                'title'=> 'Старшие менеджеры по продажам',
                                                'id'=> null,
                                            ]
                                        ]
                                    ],
                                    [
                                        'title'=> 'КЦ AZ',
                                        'id'=> null,
                                        'childs'=>[
                                            [
                                                'title'=> 'Тим лидеры',
                                                'id'=> null,
                                            ],
                                            [
                                                'title'=> 'Операторы',
                                                'id'=> null,
                                            ],
                                        ]
                                    ],
                                    [
                                        'title'=> 'КЦ Аутсорс',
                                        'id'=> null,
                                        'childs'=>[
                                            [
                                                'title'=> 'Тим лидеры',
                                                'id'=> null,
                                            ],
                                            [
                                                'title'=> 'Операторы',
                                                'id'=> null,
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];


            $this->newStructureIds[]    = $org->id;

            $this->makeBindByArr2($new);

            foreach ($map as $id => $parentId){
                $this->bindOrganization($id, $parentId);
            }



            $all        = Organization::all();
            $toDelete   = [];
            foreach ($all as $oldOrg) {
                if (!in_array($oldOrg->id, $this->newStructureIds)) {
                    $toDelete[] = $oldOrg->id;
                }
            }


            foreach ($toDelete as $delId) {
                $del    = Organization::find($delId);
                if($del) {
                    $del->delete();
                    $this->line($delId . ' removed');
                }
                else {
                    $this->error($delId . ' not found');
                }
            }

            Organization::rebuild(true);


            $all    = Organization::find($org->id)->getDescendantsAndSelf();
            foreach ($all as $org) {
                $str    = str_repeat("\t", $org->depth)
                        . $org->parent_id . ':' . $org->id
                        . ': ' . $org->title;

                $this->line($str);
            }

        });


    }

    public function bindOrganization($id, $parentId)
    {
        if(!in_array($id, $this->newStructureIds)) {
            $this->newStructureIds[]    = $id;
        }

        $organization       = Organization::find($id);
        $parentOrganization = Organization::find($parentId);
        $organization->makeChildOf($parentOrganization);

        return true;
    }

    public function makeBindByArr2($data)
    {
        $parent = Organization::find(array_get($data, 'id'));
        if(!$parent) {
            $parent = Organization::create(['title' => $data['title']]);
        }

        $childs = (array)array_get($data, 'childs');
        foreach ($childs as $child) {
            $childId    = $this->makeBindByArr2($child);
            $this->bindOrganization($childId, $parent->id);
        }

        return $parent->id;

//        global $depricateId;
//
//        if($arr['id']!==null){
//            $parentOrgId = $arr['id'];
//        }else{
//            $parentOrg = Organization::create(['title'=>$arr['title']]);
//            $parentOrgId = $parentOrg->id;
//        }
//
//        $depricateId[$parentOrgId] = $parentOrgId;

//        if(isset($arr['childs'])){
//
//            foreach ($arr['childs'] as $child) {
//                $childId = $this->makeBindByArr2($child);
//                $depricateId[$childId] = $childId;
//                $this->bindOrganization($childId, $parentOrgId);
//            }
//        }

//        return $parentOrgId;
    }
}
