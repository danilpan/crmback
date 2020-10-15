<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\SipCallerId;
use App\Http\Requests\Api\V2\SipCallerIdRequest;
use App\Http\Requests\Api\V2\SipCallerIdUpdateRequest;
use App\Repositories\SipCallerIdRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\SipCallerIdService;
use App\Services\UsersService;
use Auth;

class SipCallerIdController extends Controller
{
    protected $usersService;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.sip_caller_ids.$type",Auth::user()->organization_id);
    }
    
    /**
     * Возвращает список всех SipCallerId
     */
    public function index(SearchRequest $request, SipCallerIdService $service){
        return $service->index();
    }

    /**
     * Создает новую запись в таблице SipCallerId
     */
    public function store(SipCallerIdRequest $request, SipCallerIdService $service)
    {
        $data = $request->validated();
        $sip_caller_id = $service->create($data, true);
        return $sip_caller_id;
    }

    /**
     * Возвращает SipCallerId по ID
     */
    public function show($id, SipCallerIdRepository $repository)
    {
        $sip_caller_id = $repository->find($id);
        if (!$sip_caller_id) {
            return $this->errorResponse('Не найдено', 404, ['sip_caller_id'=>'Caller ID с ID '.$id.' не существует']);
        }
        return $sip_caller_id;
    }

    /**
     * Редактирует SipCallerId по ID
     */
    public function update($id, SipCallerIdUpdateRequest $request, SipCallerIdService $service, SipCallerIdRepository $repository)
    {
        $sip_caller_id = $repository->find($id);
        if (!$sip_caller_id) {
            return $this->errorResponse('Не найдено', 404, ['sip_caller_id'=>'Caller ID с ID '.$id.' не существует']);
        }
        $data = $request->validated();
        $sip_caller_id = $service->update($id, $data, true);
        return $sip_caller_id;
    }

    /**
     * Удаляет SipCallerId по ID
     */
    public function destroy($id, SipCallerIdRepository $repository)
    {
        $sip_caller_id = $repository->find($id);
        if (!$sip_caller_id) {
            return $this->errorResponse('Не найдено', 404, ['sip_caller_id'=>'Caller ID с ID '.$id.' не существует']);
        }
        $repository->deleteFromIndex($sip_caller_id);
        return $repository->delete($id);
    }
    
    public function getFreePrivate(SipCallerIdService $service)
    {
        $item = $service->getFreePrivate();
        return $item;
    }
    
    public function getForIn(SipCallerIdService $service)
    {
        $with_children = true;
        $result["data"] = $service->getForIn($with_children);
        return $result;
    }
    
    /**
     * Desc
     * 
     * @method getOperators
     * @param  integer             $queue_id ID of AtsQueue
     * @param  SipCallerIdService $service  
     * @return Collection
     */
    public function getOperators($queue_id, SipCallerIdService $service)
    {
        $items = $service->getOperators($queue_id, Auth::user()->organization_id);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        $result["data"] = $items;
        return $result;
    }
}
