<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProjectCreateRequest;
use App\Http\Requests\Api\V2\ProjectUpdateRequest;
use App\Http\Requests\Api\V2\ProductProjectRequest;
use App\Repositories\ProjectsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\SuggestRequest;
use App\Services\ProjectsService;
use App\Services\ProjectPageService;
use Illuminate\Support\Facades\DB;

class ProjectsController extends Controller
{

    public function getList(SearchRequest $request, ProjectsService $service)
    {
        // $query          = $request->get('q');
        // $filters        = $request->get('filters');
        // $user           = $this->auth->user();

        // return $service->searchProjects($query, $user, $filters, $request['page']);
        if(isset($request['group'])){
            $result =  $service->dxSwitchGroup($request);
            if(isset($result))
                return $result;
        }   
        if (empty($request['take'])) {
            $request['take'] = 50;
        }
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function exToExcel(SearchRequest $request, ProjectsService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  response()->file($service->exToExcel($request)); 
    }


    public function getListWithPages(SearchRequest $request, ProjectsService $service, ProjectPageService $pageservice)
    {
        /*$projects_search = $this->search($request, $service);
        $data = [];
        $sites = [];
        $projects = [];
        foreach ($projects_search as $key => $item) {
            if(count($item['project_page'])){
                foreach ($item['project_page'] as $page) {
                    $sites[] = [
                        'id' => $page['id'],
                        'unic_id' => 's'.$page['id'],
                        'type' => 'site',
                        'name' => $page['name'],
                        'link' => $page['link'],
                        'project_id' => $item['id'],
                    ];
                }                
            }
            $projects[] = [
                'id' => $item['id'],
                'unic_id' => 'p'.$item['id'],
                'type' => 'project',
                'name' => $item['title'],
            ];
        }
        $data = array_merge($sites,$projects);
        return $data;*/
        

        // $projects = $this->search($request, $service);


        // $projects = $service->searchProjects($request['q'], $this->auth->user(), null, $request['page']);
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        
        $request['filter'] = json_encode(["title", "contains", $request['q']]);
        $request['take'] = 100;
        $request['skip'] = 0;

        $projects = $service->dxSearch($request);

        $page = $this->search($request, $pageservice);                        
        $merged = $page->merge($projects);        
        $merged = $merged->map(function($item) {
            if(isset($item['project_page'])){
                $item['unic_id'] = 'p'.$item['id'];                
            }else{
                $item['unic_id'] = 's'.$item['id'];                
            };
            return $item;
        });
        return ['data'=>$merged->all()];
    }

    public function getSuggest(SuggestRequest $request, ProjectsService $service)
    {
        $data       = $request->validated();
        $q          = array_get($data, 'q');
        $filters    = (array)array_get($data, 'filters');
        $user       = $this->auth->user();

        if(empty($q)) {
            return [];
        }

        // $models = $service->suggest($user, $q, 200, $filters);
        // $result['data'] = $service->suggestPermitted($q, $this->auth->user()['organization']['role_id']);

        $result['data'] = $service->searchProjects($q, $this->auth->user(), null, 1, 100, $sort = ['id'=>'asc'], false);

        return $result;
    }

    public function getById($id, ProjectsRepository $projectsRepository)
    {   
        $projects = $projectsRepository->find($id);
        return $projects;
    }

    public function addProducts(ProductProjectRequest $request, ProjectsService $service)
    {
        $data = $request->validated();
        $project = $service->addProducts($data);

        return $project;
    }

    public function getProducts($id, ProjectsService $service){
        $items = $service->getProducts($id);
        return $items;
    }

    public function deleteProducts($id, $project_id, ProjectsService $service)
    {        
        return $service->deleteProducts($id, $project_id);
    }

    public function create(ProjectCreateRequest $request, ProjectsService $service)
    {
        $data = $request->validated();
        $project = $service->create($data, true);

        return $project;
    }

    public function update($id, ProjectUpdateRequest $request, ProjectsService $projectsService)
    {
        
        $project = $projectsService->update($id, $request->validated(), true);

        return $project;
    }

    public function delete($id, ProjectsRepository $repository)
    {
        return $repository->delete($id);
    }
}
