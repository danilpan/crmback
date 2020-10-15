<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProjectGasketRequest;
use App\Repositories\ProjectGasketRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\ProjectGasket;

class ProjectGasketController extends Controller
{


    public function getList(SearchRequest $request, ProjectGasketRepository $repository){
        
        $items = $repository->all();

        return $items;
    }

    public function getByProjectId($id, ProjectGasketRepository $repository){
        
        $items = $repository->findAllBy("project_id",$id);

        return $items;
    }

    public function getById($id, ProjectGasketRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }



    public function create(ProjectGasketRequest $request, ProjectGasketRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);

        return $item;
    }

    public function update($id, ProjectGasketRequest $request, ProjectGasketRepository $repository)
    {
        $item = $repository->update( $request->validated(), $id, "id");
        return $item;
    }

    public function delete($id, ProjectGasketRepository $repository)
    {
        return $repository->delete($id);
    }
}
