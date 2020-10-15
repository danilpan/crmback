<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\StatusRequest;
use App\Http\Requests\Api\V2\StatusAndRoleRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\Request;
use App\Repositories\StatusesRepository;
use App\Services\StatusesService;
use App\Services\OrganizationsService;
use Auth;

class StatusesController extends Controller
{

    public function getAll(StatusAndRoleRequest $request, StatusesService $service)
    {
        return $service->getAll($request["role_id"],$request["other_role_id"], $request["status_id"]);
    }

    public function getTree(StatusAndRoleRequest $request, StatusesService $service)
    {
        return $service->getTree($request["role_id"],$request["other_role_id"], $request["status_id"]);
    }

    public function getList($key, SearchRequest $request, StatusesRepository $statusesRepository, StatusesService $service)
    {

        /*$page           = $request->get('page', 1);
        $perPage        = $request->get('per_page', 20);
        $sortKey        = $request->get('sort_key', 'id');
        $sortDirection  = $request->get('sort_direction', 'asc');

        $statuses = $statusesRepository->search($page, $perPage, $sortKey, $sortDirection);

        return $statuses;*/        


        return $service->getList($key);

        $statuses = $statusesRepository->findWhere(['parent_id' => '0'])->sortBy('id');
        //$statuses = $statusesRepository->all();

        return $statuses;
    }

    public function getOneList(SearchRequest $request, StatusesRepository $statusesRepository)
    {
        
        $statuses = $statusesRepository->all();

        return $statuses;
    }

    public function getById($id, StatusesRepository $statusesRepository, OrganizationsService $organizationsService, StatusesService $service)
    {
        
        $status = $service->getById($id);        

        return $status;

        $statuses = $statusesRepository->find($id);

        $statuses->order_organization_id = $organizationsService->getMyCompany(Auth::user()->organization_id)->id;

        $statuses->by_id = true;

        return $statuses;
    }

    public function create(StatusRequest $request, StatusesService $statusesService)
    {
        return $statusesService->create($request->validated(), true);
    }

    public function update($id, StatusRequest $request, StatusesService $statusesService)
    {
        $status = $statusesService->update($id, $request->validated(), true);

        //usleep(2000000);

        return $status;
    }

    public function delete($id, StatusesRepository $statusesRepository)
    {
        return $statusesRepository->delete($id);
    }
}
