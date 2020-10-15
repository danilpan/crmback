<?php
namespace App\Http\Controllers\Api\V2;

use App\Repositories\OrganizationsRepository;
use App\Http\Requests\Api\V2\OrganizationUpdateRequest;
use App\Http\Requests\Api\V2\OrganizationCreateRequest;
use App\Http\Requests\Api\V2\OrganizationAttachRoleRequest;
use App\Services\OrganizationsService;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\Organization;

class OrganizationsController extends Controller
{

    public function dxOrganizations(SearchRequest $request, OrganizationsService $service){
        $request = $service->dxAddPermissionsWithoutGeo($request, $this->auth->user()['organization_id']);
        $request['take'] = 1000;
        return  $service->dxSearch($request);
    }
    // Список компаний к которым имеет доступ
    public function getCompanyList($role_id, OrganizationsService $organizationService)
    {

        // $filters        = [
        //     'is_company' => [
        //         'terms' => true
        //     ]
        // ];

        // $organizations  = $organizationsRepository->search(
        //     1,
        //     500,
        //     'id',
        //     null,
        //     $filters
        // );

        // return $organizations;
        $data['data'] = $organizationService->getByRole($role_id, $this->auth->user()['organization_id']);
        return $data;
    }

    // Отделы со всеми дочерними элементами рекурсивно
    public function getOrganizations($organization_id, OrganizationsService $organizationService){
        $data['data'] = $organizationService->getOrganizations($organization_id);
        return $data;
    }

    public function getList($parentId = null, OrganizationsService $organizationsService)
    {

        $this->can('api.organizations.list');

        // $organization   = $organizationsRepository->find($parentId);
        // $this->authorize('list', $organization);

        // $filters        = [
        //     'parent_id' => [
        //         'terms' => (int)$parentId
        //     ]
        // ];

        // $organizations  = $organizationsRepository->search(
        //     1,
        //     500,
        //     'id',
        //     null,
        //     $filters
        // );
        
        $user = $this->auth->user();
        $data['data'] = $organizationsService->getList($parentId, $user);
        return $data;
    }

    public function getById($id, OrganizationsService $organizationsService)
    {
        $user = $this->auth->user();
        $organization   = $organizationsService->find($id, $user, true);

        // $this->authorize('view', $organization);

        return $organization;
    }

    public function getListByRole(OrganizationsService $organizationService){
        $data['data'] = $organizationService->list($this->auth->user()['organization_id']);
        return $data;
    }

    public function getByOrganization(OrganizationsService $organizationService){
        return $organizationService->getByOrganization($this->auth->user()['organization_id']);
    }

    public function update($id, OrganizationUpdateRequest $request, OrganizationsService $organizationsService)
    {
        $user = $this->auth->user();
        $organization   = $organizationsService->find($id,$user, true);
        $this->authorize('update', $organization);

        $organization   = $organizationsService->update($id, $request->validated(), true);
        $organization   = $organizationsService->find($id,$user, true);

        return $organization;
    }

    public function create($id, OrganizationCreateRequest $request, OrganizationsService $organizationsService)
    {
        $user = $this->auth->user();
        $organization   = $organizationsService->find($id,$user, true);
        $this->authorize('create', $organization);
        $data = $request->validated();        
        if(!isset($data['api_key']) || empty($data['api_key'])){
            $api_key = $organizationsService->getApiKey();        
            $data['api_key'] = $api_key['api_key'];   
        }
        $organization   = $organizationsService->create($id, $data, true);

        return $organization;
    }

    public function getOrgForTree($organization_id, OrganizationAttachRoleRequest $request, OrganizationsService $organizationService){
        if($request->validated())
            $role_id = $request->get('role_id');

        $data['data'] = $organizationService->getOrgForTree($organization_id, $role_id);
        return $data;
    }

    public function attachRole($id, OrganizationAttachRoleRequest $request, OrganizationsService $service)
    {
        if($request->validated())
            $role_id = $request->get('role_id');

        return $service->attachRole($id, $role_id);
    }

    public function detachRole($id, OrganizationAttachRoleRequest $request, OrganizationsService $service)
    {
        if($request->validated())
            $role_id = $request->get('role_id');

        return $service->detachRole($id, $role_id);
    }

    public function getApiKey(OrganizationsService $service)
    {
        return $service->getApiKey();
    }
}
