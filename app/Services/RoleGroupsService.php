<?php
namespace App\Services;

use App\Repositories\RoleGroupsRepository;
use Illuminate\Support\Facades\DB;

class RoleGroupsService
{
    protected $roleGroupsRepository;

    public function __construct(RoleGroupsRepository $roleGroupsRepository)
    {
        $this->roleGroupsRepository = $roleGroupsRepository;
    }
    
    public function list($organization_id, $lft, $rgt)
    {
        $role_groups = null;
        $own_role_groups   = DB::table('role_group')
            ->select('role_group.*')
            // ->selectRaw('? as own_status', ['owner'])
            ->where('role_group.creator_organization_id','=',$organization_id)
            ->get()
            ->toArray();
        

        $children_role_groups = DB::table('role_group')
            ->join('organizations', 'organizations.id', '=', 'role_group.creator_organization_id')
            ->select('role_group.*')
            // ->selectRaw('? as own_status', ['children'])
            ->where([
                ['organizations.id','>',$lft],
                ['organizations.id','<',$rgt]
            ])
            ->get()
            ->toArray();


        $parent_role_groups = DB::table('role_group')
            ->join('organizations', 'organizations.id', '=', 'role_group.creator_organization_id')
            ->select('role_group.*')
            // ->selectRaw('? as own_status', ['parent'])
            ->where([
                ['organizations.lft','<',$organization_id],
                ['organizations.rgt','>',$organization_id]
            ])
            ->get()
            ->toArray();

        $role_groups = array_merge($own_role_groups,  $children_role_groups, $parent_role_groups);

        return $role_groups;
    }

    public function create($data)
    {
      return $this->roleGroupsRepository->create($data);
    }

}
