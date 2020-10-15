<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\EntityParamRequest;
use App\Http\Requests\Api\V2\EntityAndRoleRequest;
use App\Http\Requests\Api\V2\EntityParamByRolePermittedRequest;

use App\Repositories\EntityParamsRepository;
use App\Repositories\LnkRoleEntityParamsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\EntityParam;
use App\Services\EntityParamsService;

class EntityParamsController extends Controller
{

    public function getList(SearchRequest $request, EntityParamsRepository $repository){
        
        $item = $repository->all();

        return $item;
    }

    public function getAllByEntityId($id, EntityParamsRepository $repository)
    {
        $item = $repository->findAllBy("entity_id",$id);

        return $item;
    }

    public function getAllByParentId($id, EntityParamsRepository $repository)
    {
        $item = $repository->findAllBy("parent_id",$id);

        return $item;
    }
    
    public function getById($id, EntityParamsRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }

    public function create(EntityParamRequest $request, EntityParamsRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);

        return $item;
    }

    public function update($id, EntityParamRequest $request, EntityParamsRepository $repository)
    {
        $item = $repository->update( $request->validated(), $id, "id");

        return $item;
    }

    public function delete($id, EntityParamsRepository $repository)
    {
        return $repository->delete($id);
    }

    public function getByEntityAndRole(EntityAndRoleRequest $request, EntityParamsService $service)
    {
       return $service->getByRole($request['entity_id'], $request['role_id']);
    }

    public function getByRolePermitted(EntityParamByRolePermittedRequest $request, EntityParamsService $service)
    {
        return $service->getByRolePermitted($request['entity_id'], $request['role_id'], $request['other_role_id'], $request['parent_id']);
    }
}