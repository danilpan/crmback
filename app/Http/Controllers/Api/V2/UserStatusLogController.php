<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\UserStatusLog;
use App\Http\Requests\Api\V2\UserStatusLogRequest;
use App\Http\Requests\Api\V2\UserStatusLogRefreshRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\UserStatusLogService;
use App\Services\UsersService;
use Auth;

class UserStatusLogController extends Controller
{
    protected $usersService;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.user_status_logs.$type",Auth::user()->organization_id);
    }
    
    /**
     * Возвращает список всех UserStatusLog
     */
    public function index(SearchRequest $request, UserStatusLogService $service){
        return $service->index();
    }

    /**
     * Создает новую запись в таблице UserStatusLog
     */
    public function store(UserStatusLogRequest $request, UserStatusLogService $service)
    {
        $data = $request->validated();
        $user_status_log = $service->create($data, true);
        return $user_status_log;
    }

    /**
     * Возвращает UserStatusLog по ID
     */
    public function show($id)
    {
        $user_status_log = UserStatusLog::find($id);
        if (!$user_status_log) {
            return $this->errorResponse('Не найдено', 404, ['user_status_log'=>'UserStatusLog с ID '.$id.' не существует']);
        }
        return $user_status_log;
    }

    /**
     * Редактирует UserStatusLog по ID
     */
    public function update($id, UserStatusLogRequest $request, UserStatusLogService $service)
    {
        $user_status_log = UserStatusLog::find($id);
        if (!$user_status_log) {
            return $this->errorResponse('Не найдено', 404, ['user_status_log'=>'UserStatusLog с ID '.$id.' не существует']);
        }
        $data = $request->validated();
        $user_status_log = $service->update($id, $data, true);
        return $user_status_log;
    }

    /**
     * Удаляет UserStatusLog по ID
     */
    public function destroy($id)
    {
        $user_status_log = UserStatusLog::find($id);
        if (!$user_status_log) {
            return $this->errorResponse('Не найдено', 404, ['user_status_log'=>'UserStatusLog с ID '.$id.' не существует']);
        }
        $repository->deleteFromIndex($user_status_log);
        return $repository->delete($id);
    }
    
    public function refresh(UserStatusLogRefreshRequest $request, UserStatusLogService $service)
    {
        $data = $request->validated();
        $result = $service->refresh($data);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        return response()->json(['data' => $result]);;
    }
}
