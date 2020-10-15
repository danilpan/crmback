<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProjectPagePhoneRequest;
use App\Repositories\ProjectPagePhoneRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\ProjectPagePhone;
use App\Services\ProjectPagePhoneService;

class ProjectPagePhoneController extends Controller
{


    public function getList(SearchRequest $request, ProjectPagePhoneRepository $repository){
        
        $items = $repository->all();

        return $items;
    }

    public function getByPageId($id, ProjectPagePhoneRepository $repository){
        
        $items = $repository->findAllBy("project_page_id",$id);

        return $items;
    }


    public function getById($id, ProjectPagePhoneRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }

    public function create(ProjectPagePhoneRequest $request, ProjectPagePhoneService $service)
    {
        $data = $request->validated();
        $item = $service->create($data,true);

        return $item;
    }

    public function delete($id, ProjectPagePhoneService $service)
    {
        return $service->delete($id, true);
    }
}
