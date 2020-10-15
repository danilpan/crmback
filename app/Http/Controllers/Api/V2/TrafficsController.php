<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\TrafficRequest;
use App\Repositories\TrafficsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\TrafficsService;
use App\Models\Traffics;

class TrafficsController extends Controller
{


    public function getList(SearchRequest $request, TrafficsRepository $repository){
        
        $traffic = $repository->all();

        return $traffic;
    }


    public function getById($id, TrafficsRepository $repository)
    {
        $traffic = $repository->find($id);

        return $traffic;
    }

    public function create(TrafficRequest $request, TrafficsRepository $repository)
    {
        $data = $request->validated();
        $traffic = $repository->create($data);

        return $traffic;
    }

    public function update($id, TrafficRequest $request, TrafficsRepository $repository)
    {
        $traffic = $repository->update( $request->validated(), $id, "id");

        return $traffic;
    }

    public function delete($id, TrafficsRepository $repository)
    {
        return $repository->delete($id);
    }
}
