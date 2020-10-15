<?php
namespace App\Services;
 
use App\Models\User;
use App\Repositories\Repository;

abstract class Service
{
  
    protected $permission_list;
    protected $errors = [];

    public function search(
        User $user      = null,
        $page           = 1,
        $perPage        = 20,
        $sortKey        = 'id',
        $sortDirection  = 'asc',
        $filters        = [],
        $query          = null
    )
    {
        $repository = $this->getSearchRepository();
        $filters    = $this->addSearchConditions($user, $filters);
        $models     = $repository->search($page, $perPage, $sortKey, $sortDirection, $filters, $query);

        return $models;
    }

    public function dxSearch($data, $permission_list=null)
    {
        $repository = $this->getSearchRepository();
        $models     = $repository->dxSearch($data);
        if($permission_list != null){
            $this->permission_list = $permission_list;
            $models = $this->correctDataByPermissions($models);
        }

        return $models;
    }
    
    private function correctDataByPermissions($models){
        $models->each(function ($item, $key) {
            // dd($item->projects, $key);
            foreach($this->permission_list as $p_key=>$p_value){
                if($p_key == "menu.main.orders.view_track_number" && !$p_value){
                    $item->track_number = null;
                }

                if($p_key == "menu.main.orders.view_phone_number" && !$p_value){
                    $phones = [];
                    foreach($item->phones as $phone){
                        $new_phone = "";
                        for($i = 0; $i < strlen($phone);  $i++){
                            if($i>strlen($phone)-6){
                                $new_phone .= '?';
                            }else{
                                $new_phone .= $phone[$i];
                            }
                        }
                        $phones[] = $new_phone;
                    }
                    $item->phones = $phones;
                }

                $models[$key] = $item; 
            }
        });

        return $models;
    }


    public function dxFilterCorrect($request){
        if(isset($request['filter'])){
            $filter = json_decode($request['filter']);
            if(count($filter )>1)
            if(gettype($filter[0]) == 'array' &&  gettype($filter[1]) == 'array'){
                $inserted = array( 'and' );
                array_splice( $filter, 1, 0, $inserted);
            }
            $request['filter'] = json_encode($filter);
        }
        return $request;
    }

    public function dxAddPermissionsWithoutGeo($request, $organization_id, $operator_id=null){
        $permissionQuery = $this->getPermissionQuery();
        $result = $permissionQuery->getByOrganizationId($organization_id);
        $projects = [];
        foreach($result['data']['projects'] as $p){
            array_push($projects,  ['projects.id','=', $p->id], 'or');
        }
        unset($projects[count($projects)-1]);

        $organizations = [];
        foreach($result['data']['organizations'] as $p){
            array_push($organizations,  ['organizations.id','=', $p->id], 'or');
        }
        unset($organizations[count($organizations)-1]);       

        $can_only_own_orders = [];
        if($operator_id !=null)
        {
            $can_only_own_orders = ['operator_id','=',$operator_id];
        }

        $filter = json_decode($request['filter']);
        
        if(!empty($projects))
            $permissions = [$projects, 'or', $organizations];        
        else{
            $permissions = [$organizations];
        }
        if($filter != null){
            $filter =[$filter, 'and', $permissions];
            if($operator_id !=null){
                $filter[] = 'and';
                $filter[] = $can_only_own_orders;
            }
        }else{
            $filter =$permissions;
            if($operator_id !=null)
                $filter = [$filter,'and',$can_only_own_orders];
        }        
        $request['filter'] = json_encode($filter);
        $request = $this->dxFilterCorrect($request);
        return $request;
    }

    public function dxAddPermissions($request, $organization_id, $operator_id=null){
        $permissionQuery = $this->getPermissionQuery();
        $result = $permissionQuery->getByOrganizationId($organization_id);

        $projects = [];
        foreach($result['data']['projects'] as $p){
            array_push($projects,  ['projects.id','=', $p->id], 'or');
        }
        unset($projects[count($projects)-1]);

        $organizations = [];
        foreach($result['data']['organizations'] as $p){
            array_push($organizations,  ['organizations.id','=', $p->id], 'or');
        }
        unset($organizations[count($organizations)-1]);

        $geos = [];
        foreach($result['data']['geo'] as $p){
             if(isset($result['data']['is_deduct_geo']) && $result['data']['is_deduct_geo']){
                array_push($geos,  ['geo.id','<>', $p->id], 'and');
            }else{
                array_push($geos,  ['geo.id','=', $p->id], 'or');
            }

        }
        unset($geos[count($geos)-1]);

        $can_only_own_orders = [];
        if($operator_id !=null)
        {
            $can_only_own_orders = ['manager_id','=',$operator_id];
        }

        $filter = json_decode($request['filter']);

        if(!empty($projects) && !empty($geos)){
            $permissions = [$geos, 'and', $organizations];
            $permissions = [$permissions, 'or', $projects];
        }
        elseif(!empty($projects))
            $permissions = [$projects, 'or', $organizations];
        elseif(!empty($geos))
            $permissions = [$geos, 'and', $organizations];
        else{
            $permissions = [$organizations];
        }
        if($filter != null){
            $filter =[$filter, 'and', $permissions];
            if($operator_id !=null){
                $filter[] = 'and';
                $filter[] = $can_only_own_orders;
            }
        }else{
            $filter =$permissions;
            if($operator_id !=null)
                $filter = [$filter,'and',$can_only_own_orders];
        }
        $request['filter'] = json_encode($filter);

        $request = $this->dxFilterCorrect($request);
        return $request;
    }
    
    public function exToExcel($request, $permission_list=null)
    {
        $request['skip'] = 0;
        $request['take'] = $request['total'];

        $repository = $this->getSearchRepository();

        $models     = $repository->dxSearch($request);

        if($permission_list != null){
            $this->permission_list = $permission_list;
            $models = $this->correctDataByPermissions($models);
        }

        $items = $models->toArray();

        $columns = json_decode($request['columns']);
        $lib = $this->getExportToExcelLib();
        return $lib->exToExcel($items, $columns);
    }
    

    public function dxSwitchGroup($request){

        $group = json_decode($request['group']);
        $selector = $group[0]->selector;
        $items = explode(".", $selector);
        if(count($items)<2)
            return;

        $item = explode(".", $selector)[0];
        $item = $this->underscoreToCamelCase($item, true);
        $client = new \Elasticsearch\ClientBuilder();
        $hosts = [
            [
                'host' =>env("ELASTICSEARCH_HOST", "localhost"),
                'port' => env("ELASTICSEARCH_PORT", "9200"),
                'scheme' => env("ELASTICSEARCH_SCHEME", "http"),
            ]
        ];
        $Repository = '\\App\\Repositories\\'.$item.'Repository';
        $repository = new $Repository(app(), \Illuminate\Support\Collection::make(), $client->setHosts($hosts)->build());
      
        $request['group'] = str_replace($selector,  $items[1], $request['group']);

        if(isset($request['filter']) && strpos($request['filter'],  $items[0]) !== false){
            $request['filter'] = str_replace($items[0].".", "", $request['filter']);
           // $request = $this->dxClearFilter($request);
        }else{
            unset($request['filter']);
        }
        $request = $this->dxFilterCorrect($request);
               
        return $repository->dxSearch($request);
    }

    public function dadataGet($type,$data){
        $result = false;
        if ($ch = curl_init("https://dadata.ru/api/v2/clean/$type")) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token 571264e48fa7d34dde9e354600d6e621a8af0f77',
                'X-Secret: ba79f91b746b8074f7d9256d744e63c53c370c72',
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array($data)));
            $result = curl_exec($ch);
            $result = json_decode($result, true);
            curl_close($ch);
        }
            
        if($type=='phone'){
            $return_ans['type'] = $result[0]['type'];
            $return_ans['country_code'] = $result[0]['country_code'];
            $return_ans['city_code'] = substr($data, 1, 5);
            $return_ans['original_code'] = $result[0]['city_code'];
            $return_ans['provider'] = $result[0]['provider'];
            $return_ans['region'] = $result[0]['region'];
            $return_ans['time_zone'] = $result[0]['timezone'];
        }
        return $return_ans;
    }

    function underscoreToCamelCase($string, $capitalizeFirstCharacter = false) 
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    public function dxClearFilter($request){
        $filter = json_decode($request['filter']);
        $filter = $filter[0][0];
        if(gettype($filter)=='array')
            $request['filter'] = json_encode($filter);
        return $request;
    }

    public function suggest(User $user = null, $query, $total = 20, $filters = [])
    {
        $repository = $this->getSearchRepository();
        $filters    = $this->addSuggestConditions($user, $filters);
        $models     = $repository->suggest($query, $total, $filters);

        return $models;
    }

    abstract protected function addSearchConditions(User $user = null, array $filters = null);
    // {
    //     //TODO: в наследниках в этой функции надо добавлять условия из Permissions
    //     return $filters;
    // }

    protected function addSuggestConditions(User $user = null, array $filters  = null)
    {
        //TODO: в наследниках в этой функции надо добавлять условия из Permissions
        return $filters;
    }

    /**
     * Записывает в массив ошибок данные для вызова errorResponse
     * @method pushError
     * @param  Array    $error массив, содержащий аргументы для вызова errorResponse
     * @return void
     * @example pushError(['Page not found', 404, ['Страница не найдена']]);
     */
    protected function pushError($error)
    {
        $this->errors[] = $error;
    }
    
    /**
     * Очищает массив ошибок
     * @method clearErrors
     * @return void
     */
    protected function clearErrors()
    {
        $this->errors = [];
    }
    
    /**
     * Возвращает true, если есть ошибки
     * @method errors
     * @return boolean
     */
    public function errors()
    {
        return count($this->errors) > 0;
    }
    
    /**
     * Возвращает массив с аргументами для вызова errorResponse
     * @method getError
     * @return Array
     * @example if ($service->errors()) {
     *              $args = $service->getError();
     *              return $this->errorResponse($args[0], $args[1], $args[2]);
     *          }
     */
    public function getError()
    {
        if (count($this->errors) == 0) {
            return false;
        } else {
            return array_pop($this->errors);
        }
    }

    /**
     * @return Repository
     */
    abstract protected function getSearchRepository();

    /**
     * @return PermissionQuery
     */
    abstract protected function getPermissionQuery();

    /**
     * @return ExportToExcel
     */
    abstract protected function getExportToExcelLib();


}
