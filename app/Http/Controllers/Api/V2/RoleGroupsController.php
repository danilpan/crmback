<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\RoleGroupRequest;
use App\Repositories\RoleGroupsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\RoleGroup;
use App\Services\RoleGroupsService;

class RoleGroupsController extends Controller
{

    public function getList(SearchRequest $request, RoleGroupsService $service){

        // $not_can = $this->can('menu.main.role_groups');
        // if($not_can) return $not_can;

        $user = $this->auth->user();
        $result["data"]=$service->list($user['organization_id'], $user->organization['lft'], $user->organization['rgt']);

        return $result;
    }

    public function getById($id, RoleGroupsRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }

    public function create(RoleGroupRequest $request, RoleGroupsService $service)
    {
        // $not_can = $this->can('menu.main.role_groups');
        // if($not_can) return $not_can;
        
        $user = $this->auth->user();
        $item = $service->create($request->validated(), $user);

        return $item;
    }

    public function update($id, RoleGroupRequest $request, RoleGroupsRepository $repository)
    {
        $item = $repository->update( $request->validated(), $id, "id");

        return $item;
    }

    public function delete($id, RoleGroupsRepository $repository)
    {
        return $repository->delete($id);
    }
}
