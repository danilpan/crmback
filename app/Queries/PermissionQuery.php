<?php
namespace App\Queries;

use Illuminate\Support\Facades\DB;

class PermissionQuery {

    public function getByOrganizationId($organization_id){
        
        $items = DB::table('lnk_role__organization_projects')
            ->join('organizations as o', 'lnk_role__organization_projects.role_id', '=', 'o.role_id')
            ->join('lnk_role__geo as lrg', 'lrg.role_id', '=', 'o.role_id')
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

        return $this->getGroupped($items, $organizations);
    }

    private function getGroupped($items, $child_organizations = null){
        $result = [];
        $result['organizations'] = [];
        $result['projects'] = [];
        $result['geo'] = [];
        $result['is_deduct_organization'] = false;
        $result['is_deduct_project'] = false;
        $result['is_deduct_geo'] = false;
        foreach($items as $item){

            if(isset($item->organization_id))
                $result['organizations'][] = $item->organization_id;

            if(isset($item->is_deduct_organization))
               $result['is_deduct_organization'] = $item->is_deduct_organization;

            if(isset($item->project_id))
                $result['projects'][] = $item->project_id;

            if(isset($item->is_deduct_project))
                $result['is_deduct_project'] = $item->is_deduct_project;

            if(isset($item->geo_id))
                $result['geo'][] = $item->geo_id;

            if (isset($item->is_deduct_geo))
                $result['is_deduct_geo'] = $item->is_deduct_geo;
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
                ->select('title','id', 'organization_id')
                ->get()->toArray();

        $geos = DB::table('geo')
                ->whereIn('id', $result['geo'])
                ->select('name_ru', 'id')
                ->get()->toArray();


        if($items->count()>0)$result['role_id'] = $items[0]->role_id;

        $result['organizations'] = $organizations;
        $result['projects'] = $projects;
        $result['geo'] = $geos;

        $data['data']=$result;
        return $data;
    }

    public function getAllAccessOrganizationIDs($organization_id){
        
        $organization = DB::table('organizations as o')
                ->where('o.id', '=', $organization_id)
                ->first();

        $childrens = DB::table('organizations as o')
                ->where([['o.lft', '>', $organization->lft],['o.rgt','<',$organization->rgt]])
                ->select('o.title','o.id')
                ->get()->pluck('id')->all();        

        $organizationByRole =DB::table('organizations')
                ->join('lnk_role__organization_projects', 'organizations.id', '=', 'lnk_role__organization_projects.organization_id')
                ->where('lnk_role__organization_projects.role_id', '=', $organization->role_id)
                ->select('organizations.*')
                ->get()->pluck('id')->all();

        return array_merge($childrens, $organizationByRole, [$organization->id]);
    }
    
    public function getAllAccessCompanyIDs($organization_id, $with_children = false){
        
        $organization = DB::table('organizations as o')
                ->where('o.id', '=', $organization_id)
                ->first();

        if ($with_children) {
            $childrens = DB::table('organizations as o')
                    ->where([['o.lft', '>', $organization->lft],['o.rgt','<',$organization->rgt],['o.is_company',true]])
                    ->select('o.title','o.id')
                    ->get()->pluck('id')->all();        

            $organizationByRole =DB::table('organizations')
                    ->join('lnk_role__organization_projects', 'organizations.id', '=', 'lnk_role__organization_projects.organization_id')
                    ->where([['lnk_role__organization_projects.role_id','=',$organization->role_id],['organizations.is_company',true]])
                    ->select('organizations.*')
                    ->get()->pluck('id')->all();
                    
            return array_values(array_unique(array_merge($childrens, $organizationByRole, [$organization->id])));
        } else {
            return [$organization->id];
        }
    }

}
