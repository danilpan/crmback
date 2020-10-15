<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Kodeine\Acl\Models\Eloquent\Role;
use Kodeine\Acl\Models\Eloquent\Permission;
use App\Models\User;
use App\Models\Organization;

class AclTest extends TestCase
{
    use RefreshDatabase;


    public function testExample()
    {
        $companies  = $this->createTestData();



//        dd(count($companies));


//        $company1   = Organization::create(['title' => 'Компания 1']);
//        $company1->makeChildOf($root);
//
//        $company1   = Organization::create(['title' => 'Компания 2']);
//        $company1->makeChildOf($root);







//        $sales1 = Organization::create(['title' => 'Компания 1']);


//        $admin  = User::create([
//            'mail'  => 'admin@admin.com'
//        ]);

    }

    protected function createTestData()
    {
//        $root       = Organization::create(['title' => 'Root category']);

        $data   = [
            'company'   => [
                'title' => 'Компания'
            ],
            'organizations' => [
                [
                    'title' => 'Продажи'
                ],
                [
                    'title' => 'Бухгалтерия'
                ],
                [
                    'title' => 'Склад'
                ],
                [
                    'title' => 'Маркетинг'
                ],
                [
                    'title' => 'Логистика'
                ]
            ]
        ];

        $return = [];
        for($i = 0; $i<5; $i++) {
            $company    = Organization::create([
                'title' => $data['company']['title'] . ($i + 1)
            ]);

//            $company->makeChildOf($root);

            $return[$i] = [
                'company'       => $company,
                'organizations' => []
            ];

            foreach ($data['organizations'] as $d) {
                $organization   = Organization::create($d);

                $organization->makeChildOf($company);

                $return[$i]['organizations'][]  = $organization;
            }
        }

        return $return;
    }
}
