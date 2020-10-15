<?php

use Illuminate\Database\Seeder;
use App\Repositories\LnkRoleEntityParamsRepository;
use App\Repositories\LnkRoleOrganizationsProjectsRepository;

class SetAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('organizations')
            ->where('id', 1)
            ->update(['role_id' => 7, 'is_company' => true]); // Администратор

        DB::table('organizations')     
            ->where('id', 2)            // Rotar o
            ->update(['role_id' => 7,  'is_company' => true]); // Администратор

        DB::table('organizations')
            ->where('id', 66)
            ->update(['is_company' => true]);
        
        DB::table('organizations')
            ->where('id', 67)
            ->update(['is_company' => true]);

        DB::table('organizations')
            ->where('id', 125)
            ->update(['is_company' => true]);

        DB::table('organizations')
            ->where('id', 101)
            ->update(['is_company' => true]);
        
        $repo = resolve(LnkRoleEntityParamsRepository::class);

        $data = [
            [
                'role_id' => 7 ,
                'entity_param_id' => 1,
                'entity_id' => 1
            ]
        ];

        foreach ($data as $item) {
            $repo->create($item);
        }

        $repo = resolve(LnkRoleOrganizationsProjectsRepository::class);

        $data = [
            [
                'role_id' => 7 ,
                'organization_id' => 67,
            ],
            [
                'role_id' => 7 ,
                'organization_id' => 125,
            ],
            [
                'role_id' => 7 ,
                'project_id' => 3730,
            ],
            [
                'role_id' => 7 ,
                'project_id' => 3729,
            ],
            [
                'role_id' => 7 ,
                'project_id' => 3731,
            ],
            [
                'role_id' => 7 ,
                'project_id' => 3734,
            ],
            [
                'role_id' => 7 ,
                'project_id' => 3735,
            ],
            [
                'role_id' => 7 ,
                'is_deduct_project' => false
            ],
            [
                'role_id' => 7 ,
                'is_deduct_organization' => false
            ]
        ];

        foreach ($data as $item) {
            $repo->create($item);
        }

        DB::table('lnk_organizations_roles')->insert(
            ['role_id' => 1, 'organization_id' => 1]
        );
        DB::table('lnk_organizations_roles')->insert(
            ['role_id' => 2, 'organization_id' => 1]
        );
        DB::table('lnk_organizations_roles')->insert(
            ['role_id' => 3, 'organization_id' => 1]
        );
        DB::table('lnk_organizations_roles')->insert(
            ['role_id' => 4, 'organization_id' => 1]
        );
        DB::table('lnk_organizations_roles')->insert(
            ['role_id' => 5, 'organization_id' => 1]
        );
        DB::table('lnk_organizations_roles')->insert(
            ['role_id' => 6, 'organization_id' => 1]
        );
        DB::table('lnk_organizations_roles')->insert(
            ['role_id' => 7, 'organization_id' => 1]
        );   
        
        



        $statuses = DB::table('statuses')            
            ->select('id')            
            ->orderBy('id')
            ->get();

            
        foreach ($statuses as $status) {
            DB::table('lnk_role__status')->insert(
                ['role_id' => 7, 'status_id' => $status->id, 'is_view'=> true, 'is_can_set'=> true]
            );   
        }   


        /*DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 14, 'is_view'=> true, 'is_can_set'=> true]
        );   
        DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 17, 'is_view'=> true, 'is_can_set'=> true]
        );   
        DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 18, 'is_view'=> true, 'is_can_set'=> true]
        );   
        DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 19, 'is_view'=> true, 'is_can_set'=> true]
        );   
        DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 58, 'is_view'=> true, 'is_can_set'=> true]
        );   
        DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 81, 'is_view'=> true, 'is_can_set'=> true]
        );   
        DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 15, 'is_view'=> true, 'is_can_set'=> true]
        );   
        DB::table('lnk_role__status')->insert(
            ['role_id' => 7, 'status_id' => 16, 'is_view'=> true, 'is_can_set'=> true]
        );*/   



    }
}
