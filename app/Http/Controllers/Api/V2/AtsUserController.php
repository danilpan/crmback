<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\AtsUser;
use App\Http\Requests\Api\V2\AtsUserRequest;
use App\Http\Requests\Api\V2\AtsUserUpdateRequest;
use App\Repositories\AtsUserRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\AtsUserService;
use App\Services\UsersService;
use Auth;
use Request;

class AtsUserController extends Controller
{
    protected $usersService;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.ats.users.$type",Auth::user()->organization_id);
    }
    
    /**
     * Возвращает список всех AtsUser
     */
    public function index(SearchRequest $request, AtsUserService $service){
        if (!$this->can("view")) {
            return $this->errorResponse('Нет доступа', 403, ['ats_users_view' => 'Вы не можете просматривать пользователей АТС']);
        }
        $list = $service->index($request, $this->can("child_view")); 
        return response()
            ->json(['data' => $list, 'total' => $list->getTotal(), 'totalCount' => $list->getTotal()]);
    }

    /**
     * Создает новую запись в таблице AtsUser
     */
    public function store(AtsUserRequest $request, AtsUserService $service)
    {
        if (!$this->can("create")) {
            return $this->errorResponse('Нет доступа', 403, ['ats_users_create' => 'Вы не можете создавать пользователей АТС']);
        }
        $data = $request->validated();
        $ats_user = $service->create($data, true);
        if ($service->errors()) {
            return $this->error($service->getError());
        }        
        return $ats_user;
    }

    /**
     * Возвращает AtsUser по ID
     */
    public function show($id, AtsUserRepository $repository)
    {
        $ats_user = $repository->find($id);
        if (!$ats_user) {
            return $this->errorResponse('Не найдено', 404, ['ats_user'=>'Пользователя АТС с ID '.$id.' не существует']);
        }
        $ref_arr = explode('/', Request::server('HTTP_REFERER'));
        $force_access = end($ref_arr) === '' && count($ref_arr) == 4;
        if (!$this->can("view") && !$force_access) {
            return $this->errorResponse('Нет доступа', 403, ['ats_users_view' => 'Вы не можете просматривать пользователей АТС']);
        }
        $ats_user->hst = $ats_user->history();
        return $ats_user;
    }

    /**
     * Редактирует AtsUser по ID
     */
    public function update($id, AtsUserUpdateRequest $request, AtsUserService $service)
    {
        $ats_user = AtsUser::find($id);
        if (!$ats_user) {
            return $this->errorResponse('Не найдено', 404, ['ats_user_edit'=>'Пользователя АТС с ID '.$id.' не существует']);
        }
        if (!$this->can("edit")) {
            return $this->errorResponse('Нет доступа', 403, ['ats_users_edit' => 'Вы не можете редактировать пользователей АТС']);
        }
        $ats_user_orgs = $ats_user->atsGroup->organizations->pluck('id')->toArray();
        if (!$this->can("child_edit") && !in_array(Auth::user()->organization_id, $ats_user_orgs)) {
            return $this->errorResponse('Нет доступа', 403, ['ats_users_edit' => 'Вы не можете редактировать пользователей АТС дочерних групп АТС']);
        }
        $data = $request->validated();
        
        $ats_user = $service->update($id, $data, true);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        return $ats_user;
    }

    /**
     * Удаляет AtsUser по ID
     */
    public function destroy($id, AtsUserRepository $repository)
    {
        $ats_user = $repository->find($id);
        if (!$ats_user) {
            return $this->errorResponse('Не найдено', 404, ['ats_user'=>'Пользователя АТС с ID '.$id.' не существует']);
        }
        if (!$this->can("delete")) {
            return $this->errorResponse('Нет доступа', 403, ['ats_users_delete' => 'Вы не можете удалять пользователей АТС']);
        }
        $repository->deleteFromIndex($sip);
        return $repository->delete($id);
    }

    public function callerIds($id, AtsUserRepository $repository)
    {
        $ats_user = $repository->find($id);
        if (!$ats_user) {
            return $this->errorResponse('Не найдено', 404, ['ats_user_edit'=>'Пользователя АТС с ID '.$id.' не существует']);
        }
        if (!$this->can('view')) {                    
            return $this->errorResponse('Нет доступа', 403, ['ats_user_edit' => 'Вы не можете просматривать пользователей АТС']);
        }
        
        return $ats_user->sipCallerIds()->get();
    }
    
    public function isOnline(AtsUserService $service)
    {
        return response()->json(['data' => $service->isOnline()]);
    }
    
    public function inCallsSwitch($val, AtsUserService $service)
    {
        $response = $service->inCallsSwitch($val);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        return response()->json(['data' => $response]);
    }
}
