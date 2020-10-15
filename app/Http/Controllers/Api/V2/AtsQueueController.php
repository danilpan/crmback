<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\AtsQueue;
use App\Models\SipCallerId;
use App\Http\Requests\Api\V2\AtsQueueRequest;
use App\Http\Requests\Api\V2\AtsQueueUpdateRequest;
use App\Http\Requests\Api\V2\AtsQueueAttachOperatorsRequest;
use App\Http\Requests\Api\V2\AtsQueueAttachCompaniesRequest;
use App\Repositories\AtsQueueRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\AtsQueueService;
use App\Services\UsersService;
use Auth;

class AtsQueueController extends Controller
{
    protected $usersService;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.ats.queues.$type",Auth::user()->organization_id);
    }
    
    /**
     * Возвращает список всех AtsQueue
     */
    public function index(SearchRequest $request, AtsQueueService $service){
        if (!$this->can("view")) {
            return $this->errorResponse("Нет доступа", 403, ["У вас нет доступа на просмотр очередей АТС"]);
        }
        
        return $service->index($request);
    }
    
    public function getByCompany($id, SearchRequest $request, AtsQueueService $service)
    {    
        $with_other = $this->can("view_other");
        
        return $service->getByCompany($id, $request, $with_other);
    }

    /**
     * Создает новую запись в таблице AtsQueue
     */
    public function store(AtsQueueRequest $request, AtsQueueService $service)
    {
        if (!$this->can("create_$request->type")) {
            return $this->errorResponse("Нет доступа", 403, ["У вас нет доступа на создание очередей АТС данного типа"]);
        }
        $data = $request->validated();
        $item = $service->create($data, true);
        return $item;
    }

    /**
     * Возвращает AtsQueue по ID
     */
    public function show($id, AtsQueueRepository $repository)
    {
        if (!$this->can("view")) {
            return $this->errorResponse("Нет доступа", 403, ["У вас нет доступа на просмотр очередей АТС"]);
        }
        $item = $repository->find($id);
        if (!$item) {
            return $this->errorResponse('Не найдено', 404, ['item'=>'AtsQueue с ID '.$id.' не существует']);
        }
        $item->hst = $item->history();
        return $item;
    }

    /**
     * Редактирует AtsQueue по ID
     */
    public function update($id, AtsQueueUpdateRequest $request, AtsQueueService $service, AtsQueueRepository $repository)
    {
        if (!$this->can("edit_$request->type")) {
            return $this->errorResponse("Нет доступа", 403, ["У вас нет доступа на редактирование очередей АТС данного типа"]);
        }
        
        $item = $repository->find($id);
        if (!$item) {
            return $this->errorResponse('Не найдено', 404, ['item'=>'AtsQueue с ID '.$id.' не существует']);
        }
        
        $data = $request->validated();
        
        $item = $service->update($id, $data, $this->can("edit_options"), true);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        return $item;
    }

    /**
     * Удаляет AtsQueue по ID
     */
    public function destroy($id, AtsQueueRepository $repository)
    {
        if (!$this->can("delete")) {
            return $this->errorResponse("Нет доступа", 403, ["У вас нет доступа на удаление очередей АТС"]);
        }
        $item = $repository->find($id);
        if (!$item) {
            return $this->errorResponse('Не найдено', 404, ['item'=>'AtsQueue с ID '.$id.' не существует']);
        }
        $repository->deleteFromIndex($item);
        return $repository->delete($id);
    }
    
    public function attachOperators($id, AtsQueueAttachOperatorsRequest $request, AtsQueueRepository $repository, AtsQueueService $service)
    {
        $item = AtsQueue::find($id);
        if (!$item) {
            return $this->errorResponse('Не найдено', 404, ['item'=>'AtsQueue с ID '.$id.' не существует']);
        }
        $data = $request->validated();
        $caller_ids = [];

        for ($i=0; $i < count($data['caller_ids']); $i++) {
            $sorting = 1;
            if (array_key_exists('sorting', $data) && isset($data['sorting'][$i])) {
                $sorting = $data['sorting'][$i];
            }
            $caller_ids[$data['caller_ids'][$i]] = ["sorting" => $sorting];
        }
        
        $item->callerIdsOper()->sync($caller_ids);
        $item = $repository->find($id);
        $repository->reindexModel($item, true);
        
        return AtsQueue::find($id);
    }
    
    public function attachTrunks($id, AtsQueueAttachOperatorsRequest $request, AtsQueueRepository $repository, AtsQueueService $service)
    {
        if (!$this->can("edit_options")) {
            return $this->errorResponse("Нет доступа", 403, ["У вас нет доступа на редактирование спец. настроек и связей очередей АТС"]);
        }
        
        $data = $request->validated();
        
        $service->attachTrunks($id, $data["caller_ids"]);
        $error = null;
        if ($service->errors()) {
            $error = $service->getError();
            if ($error[1] != 207) {
                return $this->error($error);
            }            
        }
        
        $item = $repository->find($id);
        $repository->reindexModel($item, true);
        $trunks = AtsQueue::find($id)->callerIdsIn;
        return response()
            ->json([
                        'data' => $trunks, 
                        'total' => $trunks->count(), 
                        'message' => $error[0], 
                        'errors' => $error[2],
                    ], $error ? $error[1] : 201);
        return ;
    }
    
    public function attachCompanies($id, AtsQueueAttachCompaniesRequest $request, AtsQueueService $service, AtsQueueRepository $repository)
    {
        $item = AtsQueue::find($id);
        if (!$item) {
            return $this->errorResponse('Не найдено', 404, ['item'=>'AtsQueue с ID '.$id.' не существует']);
        }
        $data = $request->validated();
        $companies = array_key_exists('companies', $data) ? $data['companies'] : [];
        if (!$service->canAttachCompanies($id, $companies)) {
            return $this->error($service->getError());
        };
        
        $item->organizations()->sync($companies);
        $item = $repository->find($id);
        $repository->reindexModel($item, true);
        
        return AtsQueue::find($id);
    }
        public function reconfigure($id, AtsQueueService $service)
    {
        $result = $service->reconfigure($id);
        
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        
        return $result;
    }
}
