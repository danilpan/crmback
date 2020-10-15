<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\OutRoute;
use App\Http\Requests\Api\V2\OutRouteRequest;
use App\Http\Requests\Api\V2\OutRouteUpdateRequest;
use App\Repositories\OutRouteRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\OutRouteService;
use App\Services\UsersService;
use Auth;

class OutRouteController extends Controller
{
    protected $usersService;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.ats.out_routes.$type",Auth::user()->organization_id);
    }
    
    /**
     * Возвращает список всех OutRoute
     */
    public function index(SearchRequest $request, OutRouteService $service){
        if (!$this->can("view")) {
            return $this->errorResponse("Нет доступа", 403, ["Вы не можете просматирвать маршруты"]);
        }
        // Если пользователь не может просматривать группы дочерних организаций, 
        // то и маршруты дочерних он тоже не видит
        $view_children = $this->usersService->can("menu.main.ats.child_company_group_view", Auth::user()->organization_id);
        $list['data'] = $service->getAllAccessOutRoutes($view_children, false, $request->toArray());
        return $list;
    }

    /**
     * Создает новую запись в таблице OutRoute
     */
    public function store(OutRouteRequest $request, OutRouteService $service)
    {
        if (!$this->can("create")) {
            return $this->errorResponse("Нет доступа", 403, ["Вы не можете создавать маршруты"]);
        }
        $data = $request->validated();
        $out_route = $service->create($data, true);
        return $out_route;
    }

    /**
     * Возвращает OutRoute по ID
     */
    public function show($id, OutRouteRepository $repository, OutRouteService $service)
    {
        if (!$this->can("view")) {
            return $this->errorResponse("Нет доступа", 403, ["Вы не можете просматирвать маршруты"]);
        }
        $model = $repository->find($id);
        if (!$model) {
            return $this->errorResponse('Не найдено', 404, ['out_route'=>'OutRoute с ID '.$id.' не существует']);
        }
        $view_children = $this->usersService->can("menu.main.ats.child_company_group_view", Auth::user()->organization_id);
        $accessed = $service->getAllAccessOutRoutes($view_children, true);
        if (!in_array($model->id, $accessed)) {
            return $this->errorResponse("Нет доступа", 403);
        }
        
        $model->hst = $model->history();
        return $model;
    }

    /**
     * Редактирует OutRoute по ID
     */
    public function update($id, OutRouteUpdateRequest $request, OutRouteService $service, OutRouteRepository $repository)
    {
        if (!$this->can("edit")) {
            return $this->errorResponse("Нет доступа", 403, ["Вы не можете редактировать маршруты"]);
        }
        $model = $repository->find($id);
        if (!$model) {
            return $this->errorResponse('Не найдено', 404, ['out_route'=>'OutRoute с ID '.$id.' не существует']);
        }
        
        $edit_children = $this->usersService->can("menu.main.ats.child_company_group_edit", Auth::user()->organization_id);
        $accessed = $service->getAllAccessOutRoutes($edit_children, true);
        if (!in_array($model->id, $accessed)) {
            return $this->errorResponse("Нет доступа", 403);
        }
        
        $data = $request->validated();
        $model = $service->update($id, $data, true);
        return $model;
    }

    /**
     * Удаляет OutRoute по ID
     */
    public function destroy($id, OutRouteRepository $repository)
    {
        if (!$this->can("delete")) {
            return $this->errorResponse("Нет доступа", 403, ["Вы не можете удалять маршруты"]);
        }
        $out_route = $repository->find($id);
        if (!$out_route) {
            return $this->errorResponse('Не найдено', 404, ['out_route'=>'OutRoute с ID '.$id.' не существует']);
        }
        $repository->deleteFromIndex($out_route);
        return $repository->delete($id);
    }
}
