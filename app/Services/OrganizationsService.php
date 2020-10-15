<?php
namespace App\Services;

use App\Repositories\OrganizationsRepository;
use App\Repositories\PermissionsRepository;
use App\Models\Permission;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Queries\PermissionQuery;


class OrganizationsService extends Service
{
    protected $organizationsRepository;
    protected $permissionQuery;
    protected $permissionsRepository;

    public function __construct(
        OrganizationsRepository $organizationsRepository,
        PermissionsRepository $permissionsRepository,
        PermissionQuery $permissionQuery
    )
    {
        $this->organizationsRepository  = $organizationsRepository;
        $this->permissionsRepository    = $permissionsRepository;
        $this->permissionQuery = $permissionQuery;
    }

    public function list($user_organization_id)
    {
        $organization   = $this->organizationsRepository->find($user_organization_id);
        return $this->getByRole($organization["role_id"], $user_organization_id);
    }
    
    public function getOrganizations($organization_id){


        $organization = $this->organizationsRepository->find($organization_id);
        
        $organizations   = $this->organizationsRepository->findWhere([
            ['lft','>',$organization['lft']],
            ['rgt','<',$organization['rgt']]
        ]);

        $organizations[] = $organization;

        return $organizations;
    }

    public function getParentOrganizations($organization_id){
        $organization = $this->organizationsRepository->find($organization_id);

        $organizations   = $this->organizationsRepository->findWhere([
            ['lft','<',$organization['lft']],
            ['rgt','>',$organization['rgt']]
        ]);

        $organizations[] = $organization;

        return $organizations;
    }
   
    public function getByOrganization($organization_id)
    {
        return $this->permissionQuery->getByOrganizationId($organization_id);
    }
    
    public function getByRole($role_id, $organization_id)
    {
        $organization = $this->organizationsRepository->find($organization_id);

        $organizations =DB::table('organizations')
                ->join('lnk_role__organization_projects', 'organizations.id', '=', 'lnk_role__organization_projects.organization_id')
                ->where('lnk_role__organization_projects.role_id', '=', $role_id)
                ->select('organizations.*')
                ->get();

        if ($organization) {
            $organizations_child   = $this->organizationsRepository->findWhere([
                ['lft','>',$organization['lft']],
                ['rgt','<',$organization['rgt']]
            ]);
            $organizations = $organizations->merge($organizations_child);
            $organizations = $organizations->merge([$organization]);
        }

        return $organizations;
    }

    public function getList($parentId, $user, $page = 1, $size = 500){
        $main = [];

        $query['should']['terms']['id'] = array_merge($this->list($user['organization_id'])->pluck("id")->toArray(), $this->getOrganizations($user['organization_id'])->pluck("id")->toArray());
        $main['constant_score']['filter']['bool']['should'] = $query['should'];

        if(isset($parentId)){
            $query['must']['terms']['parent_id'][] = $parentId;
            $main['constant_score']['filter']['bool']['must'] = $query['must'];
        }

        return $this->organizationsRepository->searchByParams($main, ['id'=>'asc'], $page, $size, false);
    }

    public function getApiKey(){
        $api_key = str_random(32);
        $organizations = $this->organizationsRepository->findAllBy('api_key', $api_key);
        if($organizations->count() > 0){
            return $this->getApiKey();
        }else{
            return ['api_key'=>$api_key];
        }        
    }

    public function createPermission($id, $data, $reindex = false)
    {
        $organization   = $this->organizationsRepository->find($id);
        $permission     = null;
        if($organization) {
            $permission  = $this->permissionsRepository->create(array_merge($data, ['organization_id' => $id]));
        }

        return $permission;
    }

    public function create($id, $data, $reindex = false)
    {

        $parent     =    Organization::find($id);
        
        //$parent         = $this->organizationsRepository->find($id);
        $organization   = $this->organizationsRepository->create($data);

        if($parent) {
            $organization->makeChildOf($parent);
        }

        if($reindex) {
            $this->organizationsRepository->reindexModel($organization, true);
        }

        return $organization;
    }

    public function update($id, $data, $reindex = false)
    {   

        DB::transaction(function() use ($id, $data, $reindex){
        

            try{
                $organization   = $this->organizationsRepository->update($data, $id);
            }catch(Exception $e){
                return $this->errorResponse('Ошибка обновления', 500, ['message'=>json_encode($e)]);
            }

            if($organization && $reindex) {
                $this->organizationsRepository->reindexModel($organization, true);
            }
            return $organization;
        });

    }

    public function getSharedPermissions($id)
    {
        $permissions    = $this->permissionsRepository->findAllBy('organization_id', $id);
        $permissions    = $permissions->sortBy('id');

        return $permissions;
    }

    public function find($id, $user, $withPath = false, $withPermission = false, array $relations = [])
    {

        if(!$this->haveAccess($id, $user))
            return response()->json(['error' => 'not have access'], 402);

        if($id === 0) {
            $organization               = new Organization();
            $permission                 = new Permission(Permission::DEFAULT);
            $organization->permission   = $permission;

            return $organization;
        }

        $organization  = $this->organizationsRepository->with($relations)->find($id);
        if($organization && ($withPath || $withPermission)) {
            $path   = $this->organizationsRepository->getAncestors($organization);

            if($withPath) {
                $organization->path = $path;
            }

            if($withPermission) {
                $this->buildPermissions($organization, $path);
            }
        }

        return $organization;
    }

    private function haveAccess($id, $user){

        if($id == $user['organization_id'])
            return true;

        if($id > $user['organization']['lft'] && $id < $user['organization']['rgt'])
            return true;
        
        $organizations = $this->getByRole($user['organization']['role_id'], $user['organization_id']);
        foreach($organizations as $o){
           if($o->id==$id) 
            return true;
        }
        
        return false;
    }

    public function updatePermissions($id, $data)
    {
        $organization   = $this->organizationsRepository->with(['permission'])->find($id);


        if($organization) {
            if(!$organization->permission || !$organization->permission->organization_id) {
                $permission = $this->permissionsRepository->create(array_merge($data, ['organization_id' => $organization->id]));
                $this->organizationsRepository->update(['permission_id' => $permission->id], $organization->id);
            }
            else {
                $permission = $this->permissionsRepository->updateRich($data, $organization->permission->id);
            }

            $organization   = $this->organizationsRepository->find($organization->id);
            $path           = $this->organizationsRepository->getAncestors($organization);

            $this->buildPermissions($organization, $path);
        }

        return $organization;
    }

    protected function buildPermissions($organization, $path)
    {
        $collection     =   $organization->newCollection(array_merge($path->all(), [$organization]))->sortByDesc('depth');
        $collection->load('permission');

        $collection->map(function($org) {
           if(!$org->parent_id) {
               $permission  = new Permission(Permission::DEFAULT);
               $org->setRelation('permission', $permission);
           }
        });


        $final  = null;
        foreach ($collection as $org) {
            if($org->permission) {
                $data   = $org->parent_id ? array_only($org->permission->toArray(), Permission::PERMISSION_FIELDS) : Permission::DEFAULT;
                $data   = array_dot($data);
                $data   = array_filter($data, function($val){
                    return !empty($val);
                });

                if(!$final) {
                    $final      = $data;

                    continue;
                }

                $final  = array_only($final, array_keys($data));
            }
        }

        if(!$organization->permission) {
            $permission  = new Permission();
            $organization->setRelation('permission', $permission);
        }

        $organization->permission->fill(array_undot($final));
    }

    public function getMyCompany($organization_id){

        $organization   = $this->organizationsRepository->find($organization_id);

        if(!isset($organization['parent_id']))
            return $organization;
        
        if($organization['is_company'])
            return $organization;

        return $this->getMyCompany($organization['parent_id']);
    }

    public function getOrgForTree($organization_id, $role_id){
        $organization = $this->organizationsRepository->find($organization_id);

        $organizations   = $this->organizationsRepository->findWhere([
            ['parent_id', $organization_id],
        ]);
        
        foreach($organizations as $o){
            $o["text"] = $o["title"];
            $o["value"] =$o["title"];
            if($o->roles()->where('role_id', $role_id)->first()!=null){
                $o["selected"] = true;
            }else{
                $o["selected"] = false;
            }
            $o["disabled"] = false;
            $o["isLeaf"] = false;
        }

        return $organizations;
    }

    public function getChildsById($parent_id)
    {       

        $temp = $this->organizationsRepository->searchByParams(['match' => [
                'parent_id' => $parent_id,
            ]
        ], 
        ['id'=>'asc'],1,10000,false); 

        $orgs = $temp->map(function($org){
            $org->childs = $this->getChildsById($org['id']);
            return $org;
        });   
        
        return $orgs;

    }

    public function attachRole($org_id, $role_id){
        $organization   = $this->organizationsRepository->find($org_id);
        return $organization->roles()->attach($role_id);
    }

    public function detachRole($org_id, $role_id){
        $organization   = $this->organizationsRepository->find($org_id);
        return $organization->roles()->detach($role_id);
    }


    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    protected function getSearchRepository()
    {
        return $this->organizationsRepository;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }
    protected function getExportToExcelLib(){
        return null;
    }

    public function attachAts($organization_id, $ats_id){
        $organization = $this->organizationsRepository->find($organization_id);
        return $organization->ats()->attach($ats_id);
    }

    public function detachAts($organization_id, $ats_id){
        $organization = $this->organizationsRepository->find($organization_id);
        return $organization->ats()->detach($ats_id);
    }
}
