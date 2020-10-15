<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProjectPageRequest;
use App\Repositories\ProjectPageRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\ProjectPageService;
use App\Models\ProjectPage;

class ProjectPageController extends Controller
{

    public function dxProjectPage(SearchRequest $request, ProjectPageService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function getList(SearchRequest $request, ProjectPageService $service)
    {
        return $this->search($request, $service);
    }

    public function getByProjectId($id, ProjectPageRepository $repository){
        
        $items = $repository->findAllBy("project_id",$id);

        return $items;
    }

    public function getById($id, ProjectPageRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }

    public function create(ProjectPageRequest $request, ProjectPageService  $service)
    {
        $data = $request->validated();
        $item = $service->create($data,true);

        return $item;
    }

    public function update($id, ProjectPageRequest $request, ProjectPageService $service)
    {
        $item = $service->update($id, $request->validated(),true );
        return $item;
    }

    public function delete($id, ProjectPageService $service)
    {
        return $service->delete($id, true);
    }
}
