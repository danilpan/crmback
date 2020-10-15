<?php
namespace App\Services;

use App\Models\LnkRoleStatus;
use App\Models\Status;
use App\Repositories\LnkRoleGeoRepository;
use App\Repositories\RolesRepository;
use App\Repositories\LnkRoleEntityParamsRepository;
use App\Repositories\LnkRoleStatusRepository;
use App\Repositories\LnkRoleOrganizationsProjectsRepository;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Queries\PermissionQuery;

class RolesService extends Service
{
    protected $rolesRepository;
    protected $lnkREPRepository;
    protected $lnkRoleStatusRepository;
    protected $lnkRoleOrganizationsProjectsRepository;
    protected $lnkRoleGeoRepository;
    protected $permissionsRepository;
    protected $permissionQuery;
    
    public function __construct(
        RolesRepository $rolesRepository, 
        LnkRoleEntityParamsRepository $lnkREPRepository,
        LnkRoleStatusRepository $lnkRoleStatusRepository,
        LnkRoleOrganizationsProjectsRepository $lnkRoleOrganizationsProjectsRepository,
        LnkRoleGeoRepository $lnkRoleGeoRepository,
        PermissionQuery $permissionQuery
    )
    {
        $this->rolesRepository = $rolesRepository;
        $this->lnkREPRepository = $lnkREPRepository;
        $this->lnkRoleStatusRepository = $lnkRoleStatusRepository;
        $this->lnkRoleOrganizationsProjectsRepository = $lnkRoleOrganizationsProjectsRepository;
        $this->lnkRoleGeoRepository = $lnkRoleGeoRepository;
        $this->permissionQuery = $permissionQuery;
    }

    public function create($data){
        return $this->rolesRepository->create($data);
    }

    public function attach($data)
    {
    
        $role = null;
        $role = $this->rolesRepository->find($data['role_id']);
        foreach($data['entity_params'] as $item){

            if($item['selected']){
                $role->entity_params()->attach($item['id'],['entity_id' => $data['entity_id'], 'role_id' =>$data['role_id']]);
            }else{
                DB::table('lnk_role__entity_param')
                ->where([
                    'role_id' => $data['role_id'],
                    'entity_id' => $data['entity_id'],
                    'entity_param_id' => $item['id'],
                ])
                ->delete();
            }
        }

        return $role;
    }


    public function attachStatus($data)
    {
        $rep = null;
        $rep = $this->lnkRoleStatusRepository->findWhere([
                                        'role_id' => $data['role_id'],
                                        'status_id' => $data['status_id']
                                    ])->first();

        $item = null;

        if($data['status_param_change'] == "show"){
            $item = [
                'role_id' => $data['role_id'],
                'status_id' => $data['status_id'],
                'is_view' => $data['status_param_value']
            ];
        }else{
            $item = [
                'role_id' => $data['role_id'],
                'status_id' => $data['status_id'],
                'is_can_set' => $data['status_param_value']
            ];
        }
        
        if($rep != null){
            $rep = $this->lnkRoleStatusRepository->update($item, $rep['id']);
        }else{
            $rep = $this->lnkRoleStatusRepository->create($item);
        }

        $childNodesInfo = $this->getChildNodesInfo($rep->status_id, $data['role_id']);
        $rep->all_child_chosen = $childNodesInfo['all_child_chosen'];   // ставим/убираем галочку в текущем статусе - возвращает выбраны ли все дочерние статуса
        $rep->has_child_chosen = $childNodesInfo['has_child_chosen'];   // ставим/убираем галочку в текущем статусе - возвращает выбран ли хоть один дочерний

        $result['data'][]['result'] = $rep;
        $result['total'] = 1;
        return $rep;
    }

    public function attachAllChildStatus($data)
    {
        $item = [
            'role_id' => $data['role_id'],
            'status_id' => $data['status_id'],
            'status_param_value' => $data['status_param_value'],
            'status_param_change' => "show"
        ];
        $result = $this->attachStatus($item);

       $item = [
            'role_id' => $data['role_id'],
            'status_id' => $data['status_id'],
            'status_param_value' => $data['status_param_value'],
            'status_param_change' => "edit"
        ];

        $result = $this->attachStatus($item);

        $statuses = DB::table('statuses')
            ->leftJoin('organizations', 'statuses.organization_id', '=', 'organizations.id')
            ->select('statuses.*', 'organizations.title as organization_title')
            ->where('statuses.parent_id',  $data['status_id'])
            ->orderBy('id')
            ->get();

        if(count($statuses)>0){
            foreach ($statuses as $status) {
                $attach_data = [
                    'role_id' => $data['role_id'],
                    'status_id' => $status->id,
                    'status_param_value' => $data['status_param_value']
                ];
                $result = $this->attachAllChildStatus($attach_data);
            }
        }

        return $result;
    }

    public function getChildNodesInfo($status_id, $roleId){
        $status = Status::find($status_id);
        $roleStatus = LnkRoleStatus::where('status_id', $status_id)->where('role_id', $roleId)->first();
        $childIds = $status->getChildIds();
        $has_child_chosen = $all_child_chosen = false;
        $children = LnkRoleStatus::where('role_id', $roleId)->whereIn('status_id', $childIds)->get();

        if(empty($roleStatus) && empty($children)){
            $data['has_child_chosen'] = false;
            $data['all_child_chosen'] = false;
            return $data;
        }
        if($roleStatus) {
            if ($roleStatus->is_view == true && $roleStatus->is_can_set == true) {   // проверка текущего статуса
                $all_child_chosen = $has_child_chosen = true;
            }
            if ($roleStatus->is_view == true || $roleStatus->is_can_set == true) {
                $has_child_chosen = true;
            }
        }
        if($children) {
            if (count($childIds) > 0) {   // проверка всех дочерних статусов
                foreach ($childIds as $child_id) {
                    $role_status = LnkRoleStatus::where('status_id', $child_id)->where('role_id', $roleId)->first();
                    if (!empty($role_status)) {
                        if ($role_status->is_view == true || $role_status->is_can_set == true) {
                            $has_child_chosen = true;
                        }
                        if ($role_status->is_view == false || $role_status->is_can_set == false) {
                            $all_child_chosen = false;
                        }
                    } else {
                        continue;
                    }
                }
            }
        }

        $data['has_child_chosen'] = $has_child_chosen;
        $data['all_child_chosen'] = $all_child_chosen;
        return $data;
    }
/*
    public function detachAllChildStatus($data)
    {

        DB::table('lnk_role__status')
            ->where([
                'role_id' => $data['role_id'],
                'status_id' => $data['status_id'],
            ])
            ->delete();

        $statuses = DB::table('statuses')
            ->leftJoin('organizations', 'statuses.organization_id', '=', 'organizations.id')
            ->select('statuses.*', 'organizations.title as organization_title')
            ->where('statuses.parent_id',  $data['status_id'])
            ->orderBy('id')
            ->get();

        if(count($statuses)>0){
            foreach ($statuses as $status) {
                $detach_data = [
                    'role_id' => $data['role_id'],
                    'status_id' => $status['id'],
                ];
                $this->detachAllChildStatus($detach_data);
            }
        }

        return true;
    }*/


    public function attachOrganizationsProjects($data)
    {
        $rep = null;
        $is_deduct_organization = false;
        $is_deduct_project = false;
        $rep = $this->lnkRoleOrganizationsProjectsRepository->findWhere([
                                        'role_id' => $data['role_id']
                                    ]);
        
        if(count($rep) == 0){
            foreach($data['organizations'] as $organization ){
                $item = [
                    'role_id'=>$data['role_id'],
                    'organization_id'=>$organization
                ];
                $this->lnkRoleOrganizationsProjectsRepository->create($item);
            }

            foreach($data['projects'] as $project ){
                $item = [
                    'role_id'=>$data['role_id'],
                    'project_id'=>$project
                ];
                $this->lnkRoleOrganizationsProjectsRepository->create($item);
            }
            
            $item = [
                'role_id'=>$data['role_id'],
                'is_deduct_organization'=>$data['is_deduct_organization']
            ];
            $this->lnkRoleOrganizationsProjectsRepository->create($item);

            $item = [
                'role_id'=>$data['role_id'],
                'is_deduct_project'=>$data['is_deduct_project']
            ];
            $this->lnkRoleOrganizationsProjectsRepository->create($item);
            
        }else{

//Organizations
            foreach($rep as $r){
                $isDeleted = true;
                if($r['organization_id']!=null)
                {
                    foreach($data['organizations'] as $organization ){
                        if($r['organization_id']==$organization){
                            $isDeleted = false;
                        };
                    };
                    if($isDeleted){
                        $this->lnkRoleOrganizationsProjectsRepository->delete($r['id']);
                    }
                }
            };

            foreach($data['organizations'] as $organization ){
                $isInserted = true;
                foreach($rep as $r){
                    if($r['organization_id']==$organization){
                        $isInserted = false;
                    };
                };
                if($isInserted){
                    $item = [
                        'role_id'=>$data['role_id'],
                        'organization_id'=>$organization
                    ];
                    $this->lnkRoleOrganizationsProjectsRepository->create($item);
                };
            };

//Projects
            foreach($rep as $r){
                $isDeleted = true;
                if($r['project_id']!=null)
                {
                    foreach($data['projects'] as $project ){
                        if($r['project_id']==$project){
                            $isDeleted = false;
                        };
                    };
                    if($isDeleted){
                        $this->lnkRoleOrganizationsProjectsRepository->delete($r['id']);
                    }
                };
                if($r['is_deduct_organization']!=null)
                    $is_deduct_organization = $r['is_deduct_organization'];
                if($r['is_deduct_project']!=null)
                    $is_deduct_project = $r['is_deduct_project'];
            };

            foreach($data['projects'] as $project ){
                $isInserted = true;
                foreach($rep as $r){
                    if($r['project_id']==$project){
                        $isInserted = false;
                    };
                };
                if($isInserted){
                    $item = [
                        'role_id'=>$data['role_id'],
                        'project_id'=>$project
                    ];
                    $this->lnkRoleOrganizationsProjectsRepository->create($item);
                };
            };

// is_deduct_organization
// is_deduct_project

        foreach($rep as $r){

            if(!is_null($r['is_deduct_organization']) && $data['is_deduct_organization']!=$is_deduct_organization){

                $item = [
                    'role_id'=>$data['role_id'],
                    'is_deduct_organization'=>$data['is_deduct_organization']
                ];
                $this->lnkRoleOrganizationsProjectsRepository->update($item, $r['id']);
            }
        
            if(!is_null($r['is_deduct_project'])  && $data['is_deduct_project']!=$is_deduct_project){
                $item = [
                    'role_id'=>$data['role_id'],
                    'is_deduct_project'=>$data['is_deduct_project']
                ];
                $this->lnkRoleOrganizationsProjectsRepository->update($item, $r['id']);
            }
        };

        };
        $response["data"]=$rep;
        return $response;
    }

    public function attachGeos($data){
        
        $role = $this->rolesRepository->find($data['role_id']);
        
        $geos_params = [];

        foreach ($data['geos'] as $geo) {
            $geos_params[$geo] = [
                "is_deduct_geo" => $data['is_deduct_geo']
            ];
        };

        $role->geos()->sync($geos_params);


        /*
        $rep = null;
        $is_deduct_geo = false;
        $rep = $this->lnkRoleGeoRepository->findWhere([
            'role_id' => $data['role_id']
        ]);

        if(count($rep) == 0){
            foreach($data['geos'] as $geo ){
                $item = [
                    'role_id'=>$data['role_id'],
                    'geos'=>$geo
                ];
                $this->lnkRoleGeoRepository->create($item);
            }

            $item = [
                'role_id'=>$data['role_id'],
                'is_deduct_geo'=>$data['is_deduct_geo']
            ];
            $this->lnkRoleGeoRepository->create($item);

        }else{

//Geos
            foreach($rep as $r){
                $isDeleted = true;
                if($r['geo_id']!=null)
                {
                    foreach($data['geos'] as $geo ){
                        if($r['geo_id']==$geo){
                            $isDeleted = false;
                        };
                    };
                    if($isDeleted){
                        $this->lnkRoleGeoRepository->delete($r['id']);
                    }
                }
            };

            foreach($data['geos'] as $geo ){
                $isInserted = true;
                foreach($rep as $r){
                    if($r['organization_id']==$geo){
                        $isInserted = false;
                    };
                };
                if($isInserted){
                    $item = [
                        'role_id'=>$data['role_id'],
                        'geo_id'=>$geo
                    ];
                    $this->lnkRoleGeoRepository->create($item);
                };
            };

// is_deduct_geo

            foreach($rep as $r){

                if(!is_null($r['is_deduct_geo']) && $data['is_deduct_geo']!=$is_deduct_geo){
                    $item = [
                        'role_id'=>$data['role_id'],
                        'is_deduct_geo'=>$data['is_deduct_geo']
                    ];
                    $this->lnkRoleGeoRepository->update($item, $r['id']);
                }
            };
        };
        $response["data"]=$rep;
        return $response;*/

    }

    public function getGeos($role_id){
        $items = null;
        $items = $this->lnkRoleGeoRepository->findWhere([
            'role_id' => $role_id
        ]);

        return $this->getGrouppedGeos($items);
    }

    public function getOrganizationsProjects($role_id){
        $items = null;
        $items = $this->lnkRoleOrganizationsProjectsRepository->findWhere([
            'role_id' => $role_id
        ]);
        
        return $this->getGrouppedOrgAndProjects($items);
    }

    public function getPremissionByOrganizationId($organization_id){
        $items = DB::table('lnk_role__organization_projects')
            ->join('organizations as o', 'lnk_role__organization_projects.role_id', '=', 'o.role_id')
            ->select('lnk_role__organization_projects.*')
            ->where('o.id',  $organization_id)
            ->get();

        $organization = DB::table('organizations as o')
                ->where('o.id', '=', $organization_id)
                ->first();

        $organizations = DB::table('organizations as o')
                ->where([['o.lft', '>', $organization->lft],['o.rgt','<',$organization->rgt]])
                ->select('o.title','o.id')
                ->get();        

        $organizations[] = $organization;

        return $this->getGrouppedOrgAndProjects($items, $organizations);
    }

    public function getGrouppedOrgAndProjects($items, $child_organizations = null){
        $result = [];
        $result['organizations'] = [];
        $result['projects'] = [];
        $result['is_deduct_organization'] = false;
        $result['is_deduct_project'] = false;
        foreach($items as $item){

            if(isset($item->organization_id))
                $result['organizations'][] = $item->organization_id;

            if(isset($item->is_deduct_organization))
               $result['is_deduct_organization'] = $item->is_deduct_organization;

            if(isset($item->project_id))
                $result['projects'][] = $item->project_id;

            if(isset($item->is_deduct_project))
                $result['is_deduct_project'] = $item->is_deduct_project;    
        }

        $organizations = DB::table('organizations')
                ->whereIn('id', $result['organizations'])
                ->select('title','id')
                ->get();

        if($child_organizations != null){
            $organizations =  array_merge($organizations->toArray(), $child_organizations->toArray());
        }

        $projects = DB::table('projects')
                ->whereIn('id', $result['projects'])
                ->select('title','id','organization_id')
                ->get();                


        if($items->count()>0)$result['role_id'] = $items[0]->role_id;

        $result['organizations'] = $organizations;
        $result['projects'] = $projects;

        $data['data']=$result;

        return $data;
    }

    public function getGrouppedGeos($items){
        $result = [];
        $result['geos'] = [];
        $result['is_deduct_geo'] = false;

        foreach ($items as $item) {
            if(isset($item->geo_id))
                $result['geos'][] = $item->geo_id;

            if(isset($item['is_deduct_geo']))
                $result['is_deduct_geo'] = $item->is_deduct_geo;
        }

        $geos = DB::table('geo')
                ->whereIn('id', $result['geos'])
                ->select('name_ru', 'id')
                ->get();

        if($items->count()>0)$result['role_id'] = $items[0]->role_id;

        $result['geos']  = $geos;

        $data['data'] = $result;

        return $data;
    }

    public function getArrChildOrgsByOrganizationId($organization_id){

        $orgz_arr = $this->getPremissionByOrganizationId($organization_id);

        $find_orgz = [];

        foreach ($orgz_arr['data']['organizations'] as $org) {
            $find_orgz[] = $org->id;            
        }

        return $find_orgz;

    }


    protected function addSearchConditions(User $user = null, array $filters = null)
    {
        return $filters;
    }

    protected function getSearchRepository()
    {
        return $this->projectsRepository;
    }

    public function getGroupsByAccess($organization_id){
        /*$role_groups = DB::table('role_group')
            ->join('roles', 'role_group.id', '=', 'roles.group_id')
            ->join('lnk_organizations_roles as lor', 'roles.id', '=', 'lor.role_id')
            ->select('role_group.*')
            ->where('lor.organization_id',  $organization_id)
            ->distinct()
            ->get();*/

        $role_groups = DB::table('role_group')
            ->whereIn('role_group.creator_organization_id',  $this->permissionQuery->getAllAccessOrganizationIDs($organization_id))
            ->orderBy('id')
            ->get();
            
        $result["data"] = $role_groups;
        return $result;
    }

    public function getByAccess($organization_id, $group_id){
        /*$roles = DB::table('roles')
            ->join('lnk_organizations_roles as lor', 'roles.id', '=', 'lor.role_id')
            ->select('roles.*')
            ->where([['lor.organization_id' ,'=' ,  $organization_id],
                     ['roles.group_id' ,'=' ,  $group_id]])
            ->distinct()
            ->get();*/

        $roles = DB::table('roles')
            ->select('roles.*')
            ->whereIn('roles.creator_organization_id',  $this->permissionQuery->getAllAccessOrganizationIDs($organization_id))
            ->where('roles.group_id' ,'=' ,  $group_id)
            ->distinct()
            ->get();

        $result["data"] = $roles;
        return $result;
    }

    public function copySettings($role_from, $role_to){
        $l_role_entity_params_to = $this->lnkREPRepository->findWhere([
            'role_id' => $role_to
        ]);
        foreach($l_role_entity_params_to as $l){
            $this->lnkREPRepository->delete($l['id']);
        };
        $l_role_entity_params = $this->lnkREPRepository->findWhere([
            'role_id' => $role_from
        ])->toArray();
        foreach($l_role_entity_params as $l){
            $l["role_id"] = $role_to;
            unset( $l["id"]);
            $this->lnkREPRepository->create($l);
        };

        $l_rops_to = $this->lnkRoleOrganizationsProjectsRepository->findWhere([
            'role_id' => $role_to
        ]);
        foreach($l_rops_to as $l){
            $this->lnkRoleOrganizationsProjectsRepository->delete($l['id']);
        };
        $l_rops =  $this->lnkRoleOrganizationsProjectsRepository->findWhere([
            'role_id' => $role_from
        ])->toArray();
        foreach($l_rops as $l){
            $l["role_id"] = $role_to;
            unset( $l["id"]);
            $this->lnkRoleOrganizationsProjectsRepository->create( $l);
        };

        $l_r_status_to = $this->lnkRoleStatusRepository->findWhere([
            'role_id' => $role_to
        ]);
        foreach($l_r_status_to as $l){
            $this->lnkRoleStatusRepository->delete($l['id']);
        };
        $l_r_status = $this->lnkRoleStatusRepository->findWhere([
            'role_id' => $role_from
        ])->toArray();
        foreach($l_r_status as $l){
            $l["role_id"] = $role_to;
            unset( $l["id"]);
            $this->lnkRoleStatusRepository->create($l);
        };

        DB::table('lnk_organizations_roles')->where('role_id', '=',  $role_to)->delete();
        $l_organizations_roles = DB::table('lnk_organizations_roles')
            ->select('lnk_organizations_roles.*')
            ->where('lnk_organizations_roles.role_id',  $role_from)
            ->get()->toArray();

        foreach($l_organizations_roles as $l){
            DB::table('lnk_organizations_roles')
                ->insert(
                    ['role_id' => $role_to, 'organization_id' => $l->organization_id]
                );
        };

        DB::table('lnk_role__geo')->where('role_id', '=',  $role_to)->delete();
        $l_role_geo = DB::table('lnk_role__geo')
            ->select('lnk_role__geo.*')
            ->where('lnk_role__geo.role_id',  $role_from)
            ->get()->toArray();

        foreach($l_role_geo as $l){
            DB::table('lnk_role__geo')
                ->insert(
                    ['role_id' => $role_to, 'geo_id' => $l->geo_id, 'is_deduct_geo' => $l->is_deduct_geo]
                );
        };

        $role = $this->rolesRepository->find($role_to);

        return $role;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }

}
