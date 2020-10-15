<?php
namespace App\Services;

use App\Repositories\OutRouteRepository;
use App\Models\OutRoute;
use App\Models\User;
use App\Models\History;
use RuntimeException;
use Auth;
use App\Queries\OutRoutePermissionQuery as PermissionQuery;

class OutRouteService extends Service
{
    protected $repository;
    protected $permissionQuery;

    public function __construct(OutRouteRepository $repository, PermissionQuery $permissionQuery)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
    }
    
    public function writeHistory($changes, $id){

        if(isset(Auth::user()->id)){
            $user_id = Auth::user()->id;
        }else{
            $user_id = 1;
        }
        
        if(!empty($changes))
            History::create([
                'reference_table' => $this->repository->model(),
                'reference_id'    => $id,
                'actor_id'        => $user_id,
                'body'            => json_encode(['main' => $changes],JSON_UNESCAPED_UNICODE)
            ]);
    }
    
    public function index($view_children){
        return $this->repository->all();
    }
    
    public function create($data, $reindex = false)
    {
        $out_route = $this->repository->create($data);

        if ($out_route) {
            if ($reindex) {
                $this->repository->reindexModel($out_route, true);
            }
            $this->writeHistory($out_route->attributesToArray(), $out_route->id);
            return $out_route;
        }
        return false;
    }

    public function update($id, $data, $reindex = false)
    {

        $model_attributes = $this->repository->find($id)->attributesToArray();
        $updated = $this->repository->update($data, $id);
        
        if ($updated) {
            $changes = [];
            foreach ($data as $key => $value) {
                if ($data[$key] != $model_attributes[$key]) {
                    $changes[$key] = $value;
                }
            }
            $this->writeHistory($changes, $model_attributes['id']);
            
            if ($reindex) {
                $out_route = $this->repository->find($id);
                $this->repository->reindexModel($out_route, true);
            }
        }
        
        return $updated;
    }
    
    public function getAllAccessOutRoutes($view_children, $only_id = false, $filter = [])
    {
        if ($only_id) {
            $list = $this->permissionQuery->getAllAccessOutRoutes(Auth::user()->organization_id, $view_children, $filter);
            return array_column($list, 'id');
        } else {
            return $this->permissionQuery->getAllAccessOutRoutes(Auth::user()->organization_id, $view_children, $filter);
        }
    }

    protected function getSearchRepository()
    {
        return $this->repository;
    }

    protected function addSearchConditions(User $user=null, array $filters=null)
    {
        return $filters;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }
}