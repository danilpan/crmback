<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\EntityRequest;
use App\Repositories\EntitiesRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\Entity;

class EntitiesController extends Controller
{


    public function getList(SearchRequest $request, EntitiesRepository $repository){
        
        $item = $repository->all();

        return $item;
    }


    public function getById($id, EntitiesRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }

    public function create(EntityRequest $request, EntitiesRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);

        return $item;
    }

    public function update($id, EntityRequest $request, EntitiesRepository $repository)
    {
        $item = $repository->update( $request->validated(), $id, "id");

        return $item;
    }

    public function delete($id, EntitiesRepository $repository)
    {
        return $repository->delete($id);
    }
}
