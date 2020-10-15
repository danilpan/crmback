<?php
namespace App\Http\Controllers\Api\V2;

use App\Services\OrganizationsService;
use App\Repositories\PermissionsRepository;
use App\Http\Requests\Api\V2\PermissionUpdateRequest;
use App\Http\Requests\Api\V2\PermissionCreateRequest;

class PermissionsController extends Controller
{
    public function getByOrganization($id, OrganizationsService $organizationsService)
    {
        $user = $this->auth->user();
        $organization   = $organizationsService->find($id, $user, false, true);
        $permission = null;
        if(isset($organization->permission))
            $permission     = $organization->permission;

        return $permission;
    }

    public function getById($id, PermissionsRepository $permissionsRepository)
    {
        $permission = $permissionsRepository->find($id);

        return $permission;
    }

    public function createByOrganization($organizationId, PermissionCreateRequest $request, OrganizationsService $organizationsService)
    {
        $data       = $request->validated();
        $permission = $organizationsService->createPermission($organizationId, $data);

        return $permission;
    }

    public function update($id, PermissionUpdateRequest $request, PermissionsRepository $permissionsRepository)
    {
        $permission = $permissionsRepository->update($request->validated(), $id);

        return $permission;
    }

//    public function updateByOrganization($id, PermissionUpdateRequest $request, OrganizationsService $organizationsService)
//    {
//        $data           = $request->validated();
//        $organization   = $organizationsService->updatePermissions($id, $data);
//        $permission     = $organization->permission;
//
//        return $permission;
//    }

    public function getShared($id, OrganizationsService $organizationsService)
    {
        $permissions    = $organizationsService->getSharedPermissions($id);

        return $permissions;
    }
}