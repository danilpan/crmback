<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\OrdersDialStepsRequest;
use App\Services\OrdersDialStepsService;

class OrdersDialStepsController extends Controller
{

    public function createOrUpdate(OrdersDialStepsRequest $request, OrdersDialStepsService $service)
    {
        $data = $request->validated();
        $result = $service->createOrUpdate($data);
        return $result;
    }

}
