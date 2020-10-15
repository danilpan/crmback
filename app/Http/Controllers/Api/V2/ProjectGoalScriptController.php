<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProjectGoalScriptRequest;
use App\Http\Requests\Api\V2\ProjectGoalScriptUpdateRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Repositories\ProjectGoalScriptRepository;
use App\Repositories\ProductsRepository;
use App\Models\ProjectGoalScript;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class ProjectGoalScriptController extends Controller
{


    public function getList(SearchRequest $request, ProjectGoalScriptRepository $repository){        
        $items = $repository->all();
        return $items;
    }

    public function getByProjectGoalId($id, ProjectGoalScriptRepository $repository, ProductsRepository $productsRepository){                
        $items = $repository->findAllBy("project_goal_id",$id);    
        return $items;
    }

    public function getById($id, ProjectGoalScriptRepository $repository)    {
        $item = $repository->find($id);
        return $item;
    }

    public function create(ProjectGoalScriptRequest $request, ProjectGoalScriptRepository $repository)
    {
        $data = $request->validated();
        $link = Storage::disk('public_uploads')->putFile('scripts/'.$data['project_goal_id'], request()->file('file'));
        $data['link'] = $link;        
        $data['status'] = 0;
        $data['views'] = 0;
        
        $item = $repository->create($data);
        $item['cross_sales'] = [];
        return $item;
    }

    public function update($id, ProjectGoalScriptUpdateRequest $request, ProjectGoalScriptRepository $repository)
    {      
        $data = $request->validated();      
        if(isset($data['file'])){
            $item = $repository->find($id);
            Storage::disk('public_uploads')->delete($item->link);            
            $data['link'] = Storage::disk('public_uploads')->putFile('scripts/'.$item->project_goal_id, request()->file('file'));
            unset($data['file']);            
        }               

        if(isset($data['cross_sales'])){   
            $data['cross_sales'] = json_decode($data['cross_sales'], true);
            $goal_script = $repository->find($id);
            $goal_script->cross_sales()->sync($data['cross_sales']);
        }
        unset($data['cross_sales']); 
        $item = $repository->update( $data, $id, "id");      
        
        return $item;
    }

    public function delete($id, ProjectGoalScriptRepository $repository)
    {
        return $repository->delete($id);
    }
}
