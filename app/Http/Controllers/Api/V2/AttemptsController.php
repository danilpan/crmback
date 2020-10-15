<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\AttemptRequest;
use App\Http\Requests\Api\V2\Request;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Repositories\AttemptsRepository;
use App\Services\AttemptsService;
use App\Services\UsersService;

class AttemptsController extends Controller
{
    public function getList(SearchRequest $request, AttemptsService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function getById($id, AttemptsService $service)
    {
        return $service->getById($id);
    }

    public function create(AttemptRequest $request, AttemptsService $service)
    {
        //$organization_id = $this->auth->user()['organization_id'];

        return $service->create($request->validated(), true);
    }

    public function isPlateRegistered(SearchRequest $request, AttemptsService $service)
    {
        if(isset($request['num']))
            return $service->isPlateRegistered($request['num']);
        else
            return 'false';
    }

    public function delete($id, AttemptsService $service)
    {
        return $service->delete($id);
    }

    public function exToExcel(SearchRequest $request, AttemptsService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);

        return  response()->file($service->exToExcel($request));
    }
}