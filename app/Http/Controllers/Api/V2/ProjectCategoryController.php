<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProjectCategoryRequest;
use App\Repositories\ProjectCategoryRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\SuggestRequest;
use App\Models\ProjectCategory;
use App\Services\ProjectCategoryService;

class ProjectCategoryController extends Controller
{

    public function dxProjectCategory(SearchRequest $request, ProjectCategoryService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function getList(SearchRequest $request, ProjectCategoryRepository $repository){
        return $repository->all();
    }

    public function getSuggest(SuggestRequest $request, ProjectCategoryService $service)
    {
        return $this->suggest($request, $service);
    }

    public function getById($id, ProjectCategoryRepository $projectCategoryRepository)
    {
        $projectCategory = $projectCategoryRepository->find($id);

        return $projectCategory;
    }

    public function create(ProjectCategoryRequest $request, ProjectCategoryService $service)
    {
        $data = $request->validated();
        $projectCategory = $service->create($data, true);

        return $projectCategory;
    }

    public function update($id, ProjectCategoryRequest $request, ProjectCategoryService $service)
    {
        $projectCategory = $service->update($id, $request->validated(), true);

        return $projectCategory;
    }

    public function delete($id, ProjectCategoryService $service)
    {
        return $service->delete($id,true);
    }
}
