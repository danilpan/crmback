<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Ats;
use App\Http\Requests\Api\V2\AtsRequest;
use App\Http\Requests\Api\V2\AtsUpdateRequest;
use App\Repositories\AtsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\AtsService;

class AtsController extends Controller
{
    /**
     * Возвращает список всех АТС
     */
    public function index(SearchRequest $request, AtsService $service){        
        return $service->dxSearch($request);
    }

    /**
     * Создает новую запись в таблице АТС
     */
    public function store(AtsRequest $request, AtsService $service)
    {
        $data = $service->checkData($request->validated());
        $ats = $service->create($data, true);
        return $ats;
    }

    /**
     * Возвращает АТС по ID
     */
    public function show($id, AtsRepository $atsRepository)
    {
        $ats = $atsRepository->find($id);
        return $ats;
    }

    /**
     * Редактирует АТС по ID
     */
    public function update($id, AtsUpdateRequest $request, AtsService $service)
    {
        $data = $service->checkData($request->validated());
        $ats = $service->update($id, $data, true);
        return $ats;
    }

    /**
     * Удаляет АТС по ID
     */
    public function destroy($id, AtsRepository $repository)
    {
        return $repository->delete($id);
    }
    
    public function reconfigure($id, AtsService $service)
    {
        $result = $service->reconfigure($id);
        
        if ($service->errors()) {
            return $this->error($service->getError());
        }
        
        return $result;
    }
}
