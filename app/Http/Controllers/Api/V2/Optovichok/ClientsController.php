<?php
namespace App\Http\Controllers\Api\V2\Optovichok;

use App\Http\Requests\Api\V2\Optovichok\ClientRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\Optovichok\ClientsService;
use App\Services\UsersService;
use Auth;

class ClientsController extends Controller
{

    public function getList(SearchRequest $request, ClientsService $service)
    {
        $organization_id = Auth::user()->organization_id;
        if ($this->userService->can('menu.main.optovichok.view', $organization_id)){
            $request = $service->dxAddPermissions($request, $organization_id);
            $result = $service->dxSearch($request);

            return $result;
        }
        else{
            return $this->errorResponse('Нет доступа', 403, ['optovichok'=>'Нет доступа']);
        }
    }

    public function getById($id, ClientsService $service)
    {
        $organization_id = Auth::user()->organization_id;
        if ($this->userService->can('menu.main.optovichok.view', $organization_id)){
            return $service->getById($id);
        }
        else{
            return $this->errorResponse('Нет доступа', 403, ['optovichok'=>'Нет доступа']);
        }
    }

    public function create(ClientRequest $request, ClientsService $service)
    {
        $organization_id = Auth::user()->organization_id;
        if ($this->userService->can('menu.main.optovichok.create', $organization_id)){
            $data = $request->validated();

            return $service->create($data, true);
        }
        else{
            return $this->errorResponse('Нет доступа', 403, ['optovichok'=>'Нет доступа']);
        }

    }

    public function update($id, ClientRequest $request, ClientsService $service)
    {
        $organization_id = Auth::user()->organization_id;
        if ($this->userService->can('menu.main.optovichok.update', $organization_id)){
            $data = $request->validated();

            return $service->update($id, $data, true);
        }
        else{
            return $this->errorResponse('Нет доступа', 403, ['optovichok'=>'Нет доступа']);
        }
    }

    public function delete($id, ClientsService $service, UsersService $usersService)
    {
        $organization_id = Auth::user()->organization_id;
        if ($this->userService->can('menu.main.optovichok.delete', $organization_id)){
            return $service->delete($id);
        }
        else{
            return $this->errorResponse('Нет доступа', 403, ['optovichok'=>'Нет доступа']);
        }
    }

}