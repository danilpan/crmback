<?php

use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    public function run()
    {
        
        $organization = Organization::find(1);

        if(!$organization){

            $data = [
                [
                    //'id'    => 1,
                    'title' => 'ROOT',
                    'is_company' => true
                ],
                [
                    //'id'    => 2,
                    'title' => 'Zhenis',
                    'is_company' => true,
                    'parent_id' => 1
                ]
            ];

            foreach ($data as $item) {
                Organization::create($item);
            }           

        }
        
    }
}