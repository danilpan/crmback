<?php
namespace App\Services;

use App\Repositories\ProjectsRepository;
use App\Repositories\OrdersRepository;
use App\Repositories\OrganizationsRepository;
use App\Repositories\LnkRoleOrganizationsProjectsRepository;
use App\Services\OrganizationsService;
use Illuminate\Support\Facades\DB;
use App\Models\User;

use App\Queries\PermissionQuery;
use App\Libraries\ExportToExcel;

class ProjectsService extends Service
{
    protected $organizationsRepository;
    protected $projectsRepository;
    protected $geoRepository;
    protected $lnkROPRepository;
    protected $permissionQuery;
    protected $exportToExcel;
    protected $organizationsService;
    protected $ordersRepository;

    public function __construct(
        ProjectsRepository $projectsRepository, 
        OrganizationsRepository $organizationsRepository,
        LnkRoleOrganizationsProjectsRepository $lnkROPRepository,
        OrganizationsService $organizationsService,
        ExportToExcel $exportToExcel,
        PermissionQuery $permissionQuery,
        OrdersRepository $ordersRepository
    )
    {
        $this->lnkROPRepository = $lnkROPRepository;
        $this->projectsRepository = $projectsRepository;
        $this->organizationsRepository = $organizationsRepository;
        $this->organizationsService = $organizationsService;
        $this->exportToExcel = $exportToExcel;
        $this->permissionQuery = $permissionQuery;
        $this->ordersRepository = $ordersRepository;
    }

    public function create($data, $reindex = false)
    {
        $project = $this->projectsRepository->create($data);

        //$orders =$project->orders();

        if ($project) {
            if ($reindex) {
                $this->projectsRepository->reindexModel($project, true);

                 /*
                  * У нового проекта нет заказов, поэтому ругается на null
                    foreach($orders as $order)
                    $this->ordersRepository->reindexModel($order, true);
                 */
            }

            $project->geo()->attach($data['geos']);
            $project->traffics()->attach($data['traffics']);

            return $project;
        }

        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $project = null;
        if ($this->projectsRepository->update($data, $id)) {
            $project = $this->projectsRepository->find($id);

            if ($reindex) {
                $this->projectsRepository->reindexModel($project, true);
            }
            
            $project->geo()->detach();
            $project->geo()->attach($data['geos']);

            $project->traffics()->detach();
            $project->traffics()->attach($data['traffics']);

        }

        return $project;
    }

    public function suggestPermitted($q, $role_id){
        
        $lnkROPitems =  $this->lnkROPRepository->findWhere(['role_id'=>$role_id]);

        $is_deduct_organization = false;
        $is_deduct_project = false;
        $organizations = [];
        $projects = [];

        foreach($lnkROPitems as $item){
            if($item['is_deduct_organization'])
                $is_deduct_organization = true;

            if($item['is_deduct_project'])
                $is_deduct_project = true;

            if($item['organization_id'])
                $organizations[] = $item['organization_id'];
            
            if($item['project_id'])
                $projects[] = $item['project_id'];    
        }
        
        $wheries =[];

       if($q!="preload")
            $wheries[] = ['projects.title', 'like', '%'.$q.'%'];
        
        $result = [];

        if($is_deduct_organization && $is_deduct_project)
            $result = DB::table('projects')
                ->where($wheries)
                ->whereNotIn('organization_id', $organizations)
                ->whereNotIn('id', $projects)
                ->select('projects.title','projects.id','projects.organization_id')
                ->limit(100)
                ->get();
        
        if(!$is_deduct_organization && $is_deduct_project)
            $result = DB::table('projects')
                ->where($wheries)
                ->whereIn('organization_id', $organizations)
                ->whereNotIn('id', $projects)
                ->select('projects.title','projects.id','projects.organization_id')
                ->limit(100)
                ->get();

        if(!$is_deduct_organization && !$is_deduct_project){
            $result = DB::table('projects')
                ->where($wheries)
                ->whereIn('organization_id', $organizations)
                ->orWhereIn('id', $projects)
                ->select('projects.title','projects.id','projects.organization_id')
                ->limit(100)
                ->get();
        }

        if($is_deduct_organization && !$is_deduct_project){
            $result = DB::table('projects')
                ->where($wheries)
                ->whereNotIn('organization_id', $organizations)
                ->orWhereIn('id', $projects)
                ->select('projects.title','projects.id','projects.organization_id')
                ->limit(100)
                ->get();
        }

        return $result;
    }

    protected function addSearchConditions(User $user = null, array $filters = null)
    {

        $items = DB::table('lnk_role__organization_projects')
                ->where('lnk_role__organization_projects.role_id', '=', $user['organization']['role_id'])
                ->get();

        $filters = [];

        foreach($items as $item){

            if(isset($item->organization_id))
                $filters['organization_id']['terms'][] = $item->organization_id;

            if(isset($item->is_deduct_organization))
               $filters['organization_id']['exclude'] = $item->is_deduct_organization;

            // if(isset($item->project_id))
            //     $filters['id']['terms'][] = $item->project_id;

            // if(isset($item->is_deduct_project))
            //     $filters['id']['exclude'] = $item->is_deduct_project;    
        }
        
        //dd($filters);

        return $filters;
    }

    public function searchProjects($q, $user, $filters, $page = 1, $size = 20, $sort = ['id'=>'asc'], $isNeedHighlight=true){
        $q = mb_strtolower($q,'UTF-8');
        $main = [];
        // es файл в database/projects-projects.es    
        // Основной запрос
        if($q){
            //Если фраза
            if($this->word_count($q)==1){
                $query['should'][]['term']['item_id'] = $q;
                $query['should'][]['match_phrase']['title'] = $q;
                $query['should'][]['match_phrase']['description'] = $q;
                $query['should'][]['match_phrase']['project_page.link'] = $q;
                $query['should'][]['match_phrase']['project_page.phones.phone'] = $q;
                $main['constant_score']['filter']['bool']['should'] = $query['should'];
            }else{
                $query['should'][]['term']['item_id'] = $q;
                $query['should'][]['wildcard']['title'] = '*'.$q.'*';
                $query['should'][]['wildcard']['description'] = '*'.$q.'*';
                $query['should'][]['wildcard']['project_page.link'] = '*'.$q.'*';
                $query['should'][]['wildcard']['project_page.phones.phone'] = '*'.$q.'*';
                $main['constant_score']['filter']['bool']['should'] = $query['should'];
            }
        }

        $opByRole = $this->getOrganizationsProjectsByRole($user['organization']->role_id);
        $opByRole['organizations'][] = $user['company_id'];

        // Доступ 1 По проектам и организациям
        if(!$opByRole['is_deduct_organization'] && !$opByRole['is_deduct_project']){
            $filter['must']['bool']['should'][]['terms']['organization_id'] = $opByRole['organizations'];
            $filter['must']['bool']['should'][]['terms']['id'] = $opByRole['projects'];

            if(isset($filters['id']['terms'])) // фильтр по IDs
                $filter['must']['bool']['must'][]['terms']['id'] = $filters['id']['terms'];

            if(isset($filters['category_id']['terms'])) // фильтр по company_ids
                $filter['must']['bool']['must'][]['terms']['category_id'] = $filters['category_id']['terms'];

            $main['constant_score']['filter']['bool']['must'] = $filter['must'];
        }

        // Доступ 2 По организациям и за вычетом проектов
        if(!$opByRole['is_deduct_organization'] && $opByRole['is_deduct_project']){
            $filter['must']['bool']['must'][]['terms']['organization_id'] = $opByRole['organizations'];
            
            if(isset($filters['id']['terms'])) // фильтр по IDs
                $filter['must']['bool']['must'][]['terms']['id'] = $filters['id']['terms'];

            if(isset($filters['category_id']['terms'])) // фильтр по company_ids
                $filter['must']['bool']['must'][]['terms']['category_id'] = $filters['category_id']['terms'];

            $filter['must']['bool']['must_not'][]['terms']['id'] = $opByRole['projects'];
            $main['constant_score']['filter']['bool']['must'] = $filter['must'];
        }

        // Доступ 3 За вычетом организаций кроме указанных проектов
        if($opByRole['is_deduct_organization'] && !$opByRole['is_deduct_project']){
            $filter['must']['bool']['should'][]['bool']['must'][]['terms']['organization_id'] = $opByRole['organizations'];
            $filter['must']['bool']['should'][]['bool']['must_not'][]['terms']['id'] = $opByRole['projects'];
            
            if(isset($filters['id']['terms'])) // фильтр по IDs
                $filter['must']['bool']['must'][]['terms']['id'] = $filters['id']['terms'];

            if(isset($filters['category_id']['terms'])) // фильтр по company_ids
                $filter['must']['bool']['must'][]['terms']['category_id'] = $filters['category_id']['terms'];
    
            $main['constant_score']['filter']['bool']['must'] = $filter['must'];
        }

        // Доступ 4 За вычетом организаций и за вычетом указанных проектов
        if($opByRole['is_deduct_organization'] && $opByRole['is_deduct_project']){
            $filter['must_not'][]['terms']['organization_id'] = $opByRole['organizations'];
            $filter['must_not'][]['terms']['id'] = $opByRole['projects'];

            if(isset($filters['id']['terms'])) // фильтр по IDs
                $filter['must']['bool']['must'][]['terms']['id'] = $filters['id']['terms'];

            if(isset($filters['category_id']['terms'])) // фильтр по company_ids
                $filter['must']['bool']['must'][]['terms']['category_id'] = $filters['category_id']['terms'];

            $main['constant_score']['filter']['bool']['must_not'] = $filter['must_not'];
        }

        if(!isset($page))
            $page = 1;
        
        return $this->projectsRepository->searchByParams($main, $sort, $page, $size, $isNeedHighlight);
    }

    private function word_count($string) {
        $string = preg_replace('/\s+/', ' ', trim($string));
        $words = explode(" ", $string);
        return count($words);
    }

    public function getOrganizationsProjectsByRole($role_id){

        $lnkROPitems =  $this->lnkROPRepository->findWhere(['role_id'=>$role_id]);

        $is_deduct_organization = false;
        $is_deduct_project = false;
        $organizations = [];
        $projects = [];

        foreach($lnkROPitems as $item){
            if($item['is_deduct_organization'])
                $is_deduct_organization = true;

            if($item['is_deduct_project'])
                $is_deduct_project = true;

            if($item['organization_id'])
                $organizations[] = $item['organization_id'];
            
            if($item['project_id'])
                $projects[] = $item['project_id'];    
        }

        return [
            'organizations' => $organizations,
            'projects' => $projects,
            'is_deduct_organization' => $is_deduct_organization,
            'is_deduct_project' => $is_deduct_project
        ];
    }

    public function addProducts($data)
    {
        $project = $this->projectsRepository->find($data['id']);

        if ($project) {
           
            $project->products()->sync($data['products'],false);

            return $project;
        }

        return false;
    }

    public function getProducts($id)
    {
        $project = $this->projectsRepository->find($id);

        if ($project) {          
            
            return $project->products()->get();

        }

        return false;
    }

    public function deleteProducts($id, $project_id)
    {
        $project = $this->projectsRepository->find($project_id);

        if ($project) {
           
            $project->products()->detach($id);

            return $project;
        }

        return false;
    }

    protected function getSearchRepository()
    {
        return $this->projectsRepository;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return $this->exportToExcel;
    }
}