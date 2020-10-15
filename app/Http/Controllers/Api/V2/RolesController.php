<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\RoleGeoAttachRequest;
use App\Http\Requests\Api\V2\RoleRequest;
use App\Http\Requests\Api\V2\RoleEntityParamsAttachRequest;
use App\Http\Requests\Api\V2\RoleOrganizationsProjectsAttachRequest;
use App\Http\Requests\Api\V2\RoleStatusAttachRequest;
use App\Http\Requests\Api\V2\RoleGroupParamRequest;
use App\Http\Requests\Api\V2\OrganizationAttachRoleRequest;
use App\Http\Requests\Api\V2\RoleCopySettingsRequest;
use App\Repositories\RolesRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\Role;
use App\Services\RolesService;

class RolesController extends Controller
{

    public function getList(SearchRequest $request, RolesRepository $repository){
        
        $item = $repository->all();

        return $item;
    }

    public function getByGroupId($id, RolesRepository $repository)
    {
        $item = $repository->findAllBy("group_id",$id);
        $item = $item->sort();
        return $item;
    }

    public function getById($id, RolesRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }

    public function create(RoleRequest $request, RolesRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);

        return $item;
    }

    public function update($id, RoleRequest $request, RolesRepository $repository)
    {
        $item = $repository->update( $request->validated(), $id, "id");

        return $item;
    }

    public function delete($id, RolesRepository $repository)
    {
        return $repository->delete($id);
    }

    public function attachParams(RoleEntityParamsAttachRequest $request, RolesService $service)
    {
        $result = $service->attach($request->validated());
        return $result;
    }

    public function attachStatus(RoleStatusAttachRequest $request, RolesService $service)
    {
        $result = $service->attachStatus($request->validated());
        return $result;
    }

    public function attachAllChildStatus(RoleStatusAttachRequest $request, RolesService $service)
    {
        $result = $service->attachAllChildStatus($request->validated());
        return $result;
    }



    public function attachOrganizationsProjects(RoleOrganizationsProjectsAttachRequest $request, RolesService $service)
    {
        $result = $service->attachOrganizationsProjects($request->validated());
        return $result;
    }

    public function attachGeos(RoleGeoAttachRequest $request, RolesService $service){
        $result = $service->attachGeos($request->validated());
        return $result;
    }

    public function getGeos($id, RolesService $service){
        $item = $service->getGeos($id);
        return $item;
    }

    public function getOrganizationsProjects($id, RolesService $service)
    {
        $item = $service->getOrganizationsProjects($id);
        return $item;
    }

    public function getGroupsByAccess($organization_id, RolesService $service)
    {
        $items = $service->getGroupsByAccess($organization_id);
        return $items;
    }

    public function getByAccess($organization_id, RoleGroupParamRequest $request, RolesService $service)
    {
        $items = $service->getByAccess($organization_id, $request['role_group_id']);
        return $items;
    }

    public function copySettings(RoleCopySettingsRequest $request, RolesService $service){
        $result = null;
        if($request->validated())
            $result = $service->copySettings($request['role_from'], $request['role_to']);
        return $result;
    }
}
