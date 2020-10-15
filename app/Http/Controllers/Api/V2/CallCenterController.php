<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\CallCenterRequest;
use App\Repositories\CallCenterRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\CallCenter;

class CallCenterController extends Controller
{


    public function getList(SearchRequest $request, CallCenterRepository $repository){
        
        $items = $repository->all();

        return $items;
    }

    public function getById($id, CallCenterRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }



    public function create(CallCenterRequest $request, CallCenterRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);

        return $item;
    }

    public function update($id, CallCenterRequest $request, CallCenterRepository $repository)
    {
        $item = $repository->update( $request->validated(), $id, "id");
        return $item;
    }

    public function delete($id, CallCenterRepository $repository)
    {
        return $repository->delete($id);
    }
}
