<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProjectGoalRequest;
use App\Repositories\ProjectGoalRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\ProjectGoal;

class ProjectGoalController extends Controller
{


    public function getList(SearchRequest $request, ProjectGoalRepository $repository){
        
        $items = $repository->all();

        return $items;
    }

    public function getByProjectId($id, ProjectGoalRepository $repository){
        
        $items = $repository->findAllBy("project_id",$id);

        return $items;
    }

    public function getById($id, ProjectGoalRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }



    public function create(ProjectGoalRequest $request, ProjectGoalRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);

        return $item;
    }

    public function update($id, ProjectGoalRequest $request, ProjectGoalRepository $repository)
    {        
        $item = $repository->update( $request->validated(), $id, "id");
        return $item;
    }

    public function delete($id, ProjectGoalRepository $repository)
    {
        return $repository->delete($id);
    }
}
