<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\MoveUpOrdersRequest;
use App\Services\AtsMonitoringService;
use App\Services\UsersService;
use Auth;

class AtsMonitoringController extends Controller
{
    protected $usersService;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.ats.monitoring.$type", Auth::user()->organization_id);
    }
    
    public function moveUp(MoveUpOrdersRequest $request, AtsMonitoringService $service)
    {
        $data = $request->validated();
        $result = $service->moveUp($data['orders']);
        return response()->json(['data' => $result]);
    }
    
    public function moveDown(MoveUpOrdersRequest $request, AtsMonitoringService $service)
    {
        $data = $request->validated();
        $result = $service->moveDown($data['orders']);
        return response()->json(['data' => $result]);
    }
    
    public function getLags($queue_id = 0, AtsMonitoringService $service)
    {
        if (!$this->can("view")) {
            return $this->error(["Нет доступа", 403, ["У вас нет доступа на просмотр мониторинга"]]);
        }
        if (!$this->can("view_quench")) {
            return $this->error(["Нет доступа", 403, ["У вас нет доступа на просмотр отставаний"]]);
        }
        $response = $service->getLags($queue_id);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        return response()->json(['data' => $response]);
    }
    
    public function miniAnalytics(SearchRequest $request, AtsMonitoringService $service)
    {
        if (!$this->can("view")) {
            return $this->error(["Нет доступа", 403, ["У вас нет доступа на просмотр мониторинга"]]);
        }
        $queue_id = 0;
        $start    = null;
        $end      = null;
        foreach ($request->toArray() as $key => $value) {
            switch ($key) {
                case 'queue_id':
                    $queue_id = $value;
                    break;
                    
                case 'start':
                    $start    = $value;
                    break;
                    
                case 'end':
                    $end      = $value;
                    break;
            }
        }
        $response = $service->miniAnalytics($queue_id, $start, $end);
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        return response()->json(['data' => $response]);
    }
    
    public function getOperStates(AtsMonitoringService $service)
    {
        if (!$this->can("view")) {
            return $this->error(["Нет доступа", 403, ["У вас нет доступа на просмотр мониторинга"]]);
        }
        $response = $service->getOperStates();
        return response()->json(['data' => $response]);
    }
    
    public function getCurrentCalls(AtsMonitoringService $service)
    {
        if (!$this->can('view')) {
            return $this->error(["Нет доступа", 403, ["У вас нет доступа на просмотр мониторинга"]]);
        }
        $response = $service->getCurrentCalls();
        return response()->json(['data' => $response]);
    }
    
    public function getDialCoeff(AtsMonitoringService $service)
    {
        if (!$this->can('view')) {
            return $this->error(["Нет доступа", 403, ["У вас нет доступа на просмотр мониторинга"]]);
        }
        $response = $service->getDialCoeff();
        return response()->json(['data' => $response]);
    }
}
