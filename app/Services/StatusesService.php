<?php
namespace App\Services;

use App\Models\Status;
use App\Repositories\StatusesRepository;
use App\Repositories\LnkRoleStatusRepository;
use Illuminate\Support\Facades\DB;
use App\Repositories\OrdersRepository;
use App\Repositories\OrganizationsRepository;
use App\Models\User;
use App\Models\Role;
use App\Models\StatusTitle;
use App\Models\LnkRoleStatus;
use App\Services\RolesService;
use App\Services\OrganizationsService;
use App\Services\UsersService;
use App\Models\History;
use Auth;

use App\Queries\PermissionQuery;
use App\Libraries\ExportToExcel;

class StatusesService extends Service
{
    protected $statusesRepository;
    protected $lnkRoleStatusRepository;
    protected $rolesService;  
    protected $usersService;  
    protected $organizationsService;
    protected $ordersRepository;
    protected $organizationsRepository;
    protected $permissionQuery;
    protected $exportToExcel;

    public function __construct(
        StatusesRepository $statusesRepository, 
        LnkRoleStatusRepository $lnkRoleStatusRepository, 
        RolesService $rolesService,
        UsersService $usersService,
        OrganizationsService $organizationsService,
        OrdersRepository $ordersRepository,
        OrganizationsRepository $organizationsRepository,
        PermissionQuery $permissionQuery,
        ExportToExcel $exportToExcel
    )
    {
        $this->statusesRepository = $statusesRepository;
        $this->lnkRoleStatusRepository = $lnkRoleStatusRepository;
        $this->rolesService = $rolesService;
        $this->usersService = $usersService;
        $this->organizationsService = $organizationsService;
        $this->ordersRepository = $ordersRepository;
        $this->organizationsRepository = $organizationsRepository;

        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
    }

    public function create($data, $reindex = false)
    {       

        $parent_status = $this->statusesRepository->find($data['parent_id']);        

        $data['type'] = $parent_status->type;

        $organization_id = Auth::user()->organization_id;                

        $data['organization_id'] = $organization_id; 

        $status = $this->statusesRepository->create($data);

        if ($status) {

            $parentOrganizations = $this->organizationsService->getParentOrganizations($organization_id);     

            $parentOrganizations->map(function($item) use ($status) {            
                $this->lnkRoleStatusRepository->create([
                    'role_id'=>$item->role_id,
                    'status_id'=>$status->id,
                    'is_view'=>true,
                    'is_can_set'=>true,
                ]);
            });    

            if ($reindex) {
                $this->statusesRepository->reindexModel($status, true);;
            }

            return $status;
        }

        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        
        if ($this->statusesRepository->update($data, $id)) {
            
            $status = $this->statusesRepository->find($id);

            if($data['parent_id']==0){                
                $title = StatusTitle::firstOrNew([
                    'status_id'=>$id,
                    'organization_id' => $this->organizationsService->getMyCompany(Auth::user()->organization_id)->id
                ]);
                if(is_null($data['status_title'])){
                    $title->delete();
                }else{
                    $title->title = $data['status_title'];
                    $title->save();
                }
            }
            
            if ($reindex) {
                $this->statusesRepository->reindexModel($status, true);
            }

            if($status){
                if($data['status_title']){                    
                    $this->statusTitleHistory($status->id,$data['status_title']);
                }                
            }
        }    

        

        return $status;

    }

    public function getList($key=null){
        // if($key=='new_order' || $key=='0'){
        $organization_id = Auth::user()->organization_id;
        //$organization_id = $this->organizationsService->getMyCompany($organization_id)->id;
        /*}else{
            $order_info = $this->ordersRepository->searchByParams(
                ['match' => [
                    'key' => $key]
                ], 
                ['key'=>'asc']
            )->toArray(); 
            $organization_id = $order_info[0]['organization_id'];
        }

        $organization_id = 1;*/
        
        $organization_info = $this->organizationsRepository->searchById($organization_id)->toArray();              

        $lnk = $this->lnkRoleStatusRepository->with(['statuses'])->findWhere([
            'role_id' => $organization_info['role_id']            
        ])->sortBy('status_id')->sortBy(function ($q){
            return $q->statuses->sort;
        });
        
        $statuses = $lnk->map(function($item) use ($organization_id) {                        
            if($item->is_view){
                $temp = $this->statusesRepository->searchByParams(['match' => [
                        'id' => $item->status_id,
                    ]
                ], 
                ['id'=>'asc'])->load('status_titles');            

                if($temp[0]->status_titles->count() > 0){
                    $temp_name = $temp[0]->status_titles->where('organization_id', '=', $organization_id)->first();                
                    if(!empty($temp_name))$temp[0]->name = $temp_name->title;                
                }                

                $temp[0]->is_can_set = $item->is_can_set;                
                
                return $temp[0];   
            }      
        });             

        $statuses = $statuses->filter(function($value, $key) {
            return  $value != null;
        });

        if($key=='0'){
            $statuses_tree = $this->buildTree($statuses->toArray(), 0);            
        }else{
            $statuses_tree = $this->buildTree($statuses->toArray(), 0, true);            
        }

        return ['total'=>count($statuses_tree), 'data' => $statuses_tree];       

        /*dd($statuses);

        dd(LnkRoleStatus::find(2)->roles);

        $statuses->map(function($item) {
            dd($item->statuses);
        });

        dd($statuses->statuses);*/

        //$statuses = $this->statusesRepository->findWhere(['parent_id' => '0'])->sortBy('id');    






        $statuses = $statuses->map(function($item) use ($organization_id) {
            $item->title_organization_id = $organization_id;            
            $check_permission = $this->getAccessAndSelectedInfo(7,7,$item->id); 
            $item->visible = $check_permission[0]['disabled'];           
            $item->disabled = $check_permission[1]['disabled'];                     
            return $item;
        });

        return $statuses;
    }

    public function getChildsById($parent_id)
    {       

        $temp = $this->statusesRepository->searchByParams(['match' => [
                'parent_id' => $parent_id,
            ]
        ], 
        ['id'=>'asc'],1,10000,false); 

        $statuses = $temp->map(function($status){
            $status->childs = $this->getChildsById($status['id']);
            return $status;
        });   
        
        return $statuses;

    }
 
    public function getById($id){

        $organization_id = Auth::user()->organization_id;

        $check = $this->usersService->can('menu.main.dictionaries.statuses.history', $organization_id);

        $organization_id = $this->organizationsService->getMyCompany($organization_id)->id;


        $temp = $this->statusesRepository->searchByParams(['match' => [
                    'id' =>$id
                ]
            ], 
            ['id'=>'asc'])->load('status_titles'); 

            if($temp[0]->status_titles->count() > 0){
                $temp_name = $temp[0]->status_titles->where('organization_id', '=', $organization_id)->first();                
                if(!empty($temp_name))$temp[0]->status_title = $temp_name->title;                
            }                
            
            $temp[0]->can_history = $check;                

            return $temp[0];  
    }

    public function getTree($roleId, $other_role_id, $status_id){

        $items = DB::table('statuses')
            ->leftJoin('organizations', 'statuses.organization_id', '=', 'organizations.id')
            ->select('statuses.*', 'organizations.title as organization_title')
            ->where('statuses.parent_id', $status_id)
            ->orderBy('id')
            ->get();

        $items = json_decode($items, true);

        $list = [];
        
       // if($status_id > 0)
       // $list = $this->getAccessAndSelectedInfo($roleId, $other_role_id, $status_id);


        foreach($items as $item){

            // $is_view = false;
            // $is_can_set = false;
            // $is_access_view = false;
            // $is_access_can_set = false;

            // foreach($accessItems as $sItem){
            //     if($sItem['status_id'] == $item['id']){
            //         $is_access_view = $sItem['is_view'];
            //         $is_access_can_set = $sItem['is_can_set'];
            //     }
            // }

            // foreach($selectedItems as $sItem){
                
            //     if($sItem['status_id'] == $item['id']){
            //         $is_view = $sItem['is_view'];
            //         $is_can_set = $sItem['is_can_set'];
            //     }
            // }


            $i =[
                'id' => $item['id'],
                'text' => $item['name'].'('.$item['organization_title'].') ID:'.$item['id'],
                'value' => json_encode(["id"=>$item['id'],"parent_id"=>$item["parent_id"],"type"=>$item["type"]]),
                'color' => $item['color'],
                'is_work' => $item['is_work'],
                'selected' => false,
                // 'disabled'=> true,
                'isLeaf' => false
            ];

            array_push($list, $i);
            // array_push($list, $view);
            // array_push($list, $can_set);
        }

        $data['data'] = $list;

        // $data['total'] = 0;  // Добавляет задержку
        return $data;
    }

      public function getAll($roleId, $other_role_id, $status_id){

        $items = DB::table('statuses')
            ->leftJoin('organizations', 'statuses.organization_id', '=', 'organizations.id')
            ->select('statuses.*', 'organizations.title as organization_title')
            ->where('statuses.parent_id', $status_id)
            ->orderBy('id')
            ->get();

        $items = json_decode($items, true);

        $list = [];
        
        if($status_id > 0)
            $list = $this->getAccessAndSelectedInfo($roleId, $other_role_id, $status_id);

        foreach($items as $item){

            $isAllChildChecked = $this->IsAllChildsChecked($other_role_id, $item['id']);
            $child = $this->getAccessAndSelectedInfo( $roleId, $other_role_id, $item['id']);
            $childNodesInfo = $this->rolesService->getChildNodesInfo($item['id'], $other_role_id);
            $i =[
                'id' => $item['id'],
                'text' => $item['name'].'('.$item['organization_title'].') ID:'.$item['id'],
                'value' => $item['name'].' '.$item['id'],
                'selected' => $isAllChildChecked,
                'disabled'=> $child[1]["disabled"],
                'all_child_chosen' => $childNodesInfo['all_child_chosen'],   // выбраны ли все дочерние при открытии дерева
                'has_child_chosen' => $childNodesInfo['has_child_chosen']    // выбран ли хоть один дочерний при открытии
            ];

            array_push($list, $i);
        }

        $data['data'] = $list;

        return $data;
    }

    public function getAllTree($roleId, $other_role_id, $status_id){
        $items = DB::table('statuses')
            ->leftJoin('organizations', 'statuses.organization_id', '=', 'organizations.id')
            ->select('statuses.*', 'organizations.title as organization_title')
            ->where('statuses.parent_id', $status_id)
            ->orderBy('id')
            ->get();

        $items = json_decode($items, true);

        $list = [];

        foreach($items as $item){

            $children = $this->getAccessAndSelectedInfo($roleId, $other_role_id, $item['id']);
            $children_from_db = $this->getEditableTree($roleId, $other_role_id, $item['id']);
            if(count($children_from_db)>0)
                $children=array_merge($children, $children_from_db);

            $i =[
                'id' => $item['id'],
                'text' => $item['name'].'('.$item['organization_title'].') ID:'.$item['id'],
                'value' => $item['name'].' '.$item['id'],
                'selected' => false,
                'disabled'=> false,
                'isLeaf' => false,
                'children' => $children
            ];

            array_push($list, $i);
        }

        if( $status_id == 0){
            $data['data'] = $list;
            return $data;    
        }else{
            return $list;
        }
        
    }

    private function IsAllChildsChecked($other_role_id, $status_id){
         $items = DB::table('statuses')
            ->leftJoin('organizations', 'statuses.organization_id', '=', 'organizations.id')
            ->select('statuses.*', 'organizations.title as organization_title')
            ->where('statuses.parent_id', $status_id)
            ->orderBy('id')
            ->get();

        $items = json_decode($items, true);

        $result = true;

        $info = $this->isAccessAndSelectedInfoIsChecked($other_role_id, $status_id);

        if(!$info)
            return false;

        foreach($items as $item){

            $childs = $this->IsAllChildsChecked($other_role_id, $item['id']);

            if(!$childs)
                $result = false;
        
        }

        return $result;
    }

    public function isAccessAndSelectedInfoIsChecked($other_role_id, $status_id){
        $selectedItem = $this->lnkRoleStatusRepository->findWhere([
            'role_id' => $other_role_id,
            'status_id' => $status_id
        ])->first();
        if(isset($selectedItem) && $selectedItem['is_view'] && $selectedItem['is_can_set']){
            return true;
        }else{
            return false;
        };
    }

    public function getAccessAndSelectedInfo($roleId, $other_role_id, $status_id){

        $accessItem = $this->lnkRoleStatusRepository->findWhere([
            'role_id' => $roleId,
            'status_id' => $status_id
        ])->first();


        $selectedItem = $this->lnkRoleStatusRepository->findWhere([
            'role_id' => $other_role_id,
            'status_id' => $status_id
        ])->first();

        $is_access_view = false;
        $is_access_can_set = false;
        $is_view = false;
        $is_can_set = false;
        
        if(isset($accessItem)){
            $is_access_view = $accessItem['is_view'];
            $is_access_can_set = $accessItem['is_can_set'];
        };
        
        if(isset($selectedItem)){
            $is_view = $selectedItem['is_view'];
            $is_can_set = $selectedItem['is_can_set'];
        };

        if($roleId == $other_role_id){
            $is_access_view = false;
            $is_access_can_set = false;
        }

        $view = [
            'parent_id' => $status_id,
            'id' => 0,
            'text' => 'Просморт',
            'value' => 'show',
            'icon' => 'fa fa-check icon-state-success',
            'opened' => false,
            'selected' => $is_view,
            'disabled' => !$is_access_view,
            'isLeaf' => true
        ]; 

        $can_set = [
            'parent_id' => $status_id,
            'id' => 1,
            'text' => 'Возможность установки',
            'value' => 'can-set',
            'icon' => 'fa fa-warning icon-state-warning',
            'opened' => false,
            'selected' => $is_can_set,
            'disabled' => !$is_access_can_set,
            'isLeaf' => true
        ]; 

        $list = [];

        $list[] =  $view;
        $list[] =  $can_set;

        return $list;
    }


    public function buildTree($elements, $parentId = 0, $check_is_work=false) {
        $branch = array();

        foreach ($elements as $element) {
            if (!$element) continue;
            $check = true;
            if($check_is_work && $element['is_work']!="1")$check = false;
            if ($element['parent_id'] == $parentId && $check) {
                $children = $this->buildTree($elements, $element['id'], $check_is_work);
                if ($children) {
                    $element['children'] = $children;
                }else{
                    $element['children'] = [];
                }
                $branch[] = $element;
            }
        }

        return $branch;
    } 

     public function statusTitleHistory($status_id,$status_title){                
                History::create([
                    'reference_table' => $this->statusesRepository->model(),
                    'reference_id'    => $status_id,
                    'actor_id'        => Auth::user()->id,
                    'body'            => json_encode(['main' => ['status_title'=>$status_title]],JSON_UNESCAPED_UNICODE)
                ]);
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }
    
    protected function getSearchRepository()
    {
        return $this->statusesRepository;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return $this->exportToExcel;
    }
}
