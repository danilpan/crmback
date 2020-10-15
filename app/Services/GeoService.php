<?php
namespace App\Services;

use App\Repositories\GeoRepository;
use App\Models\User;
use App\Models\PhoneCodes;
use App\Repositories\LnkRoleGeoRepository;
use RuntimeException;
use Auth;
use App\Queries\PermissionQuery;

class GeoService extends Service
{
    protected $geoRepository;
    protected $permissionQuery;
    protected $lnkRroleGeoRepository;
        
    
    public function __construct(GeoRepository $geoRepository, PermissionQuery $permissionQuery, LnkRoleGeoRepository  $lnkRoleGeoRepository)
    {
        $this->geoRepository = $geoRepository;
        $this->permissionQuery = $permissionQuery;
        $this->lnkRroleGeoRepository = $lnkRoleGeoRepository;
    }    

    public function getAll(){

        return $this->search(
                    Auth::user(),
                    1,
                    240                
            );  

    } 

    public function getByPhone($phone)
    {
        
        if(substr($phone, 0, 1)=="8")
        {
            $phone = '7'.substr($phone, 1, strlen($phone));
        }

        $phone = '+'.$phone;

        for ($i = 7; $i >= 2; $i--) {
            $mask = substr($phone, 0, $i);
            $geo_info = $this->geoRepository->searchByParams(
            ['match' => [
                'mask' => $mask]
            ], 
            ['mask'=>'asc'],
            1,1,false
            )->toArray();
            
            if(!empty($geo_info))return $geo_info[0];           
        }           

    }

    public function getGeoByRole($role_id){
        $lnkRoleGeosItems = $this->lnkRroleGeoRepository->findWhere(['role_id' => $role_id]);

        $geos = [];
        $is_deduct_geo = false;

        foreach ($lnkRoleGeosItems as $item) {
            if($item['geo_id'])
                $geos[] = $item['geo_id'];
            if($item['is_deduct_geo'])
                $is_deduct_geo = true;
        }

        return [
            'geos' => $geos,
            'is_deduct_geo' => $is_deduct_geo
        ];
    }

    public function getTimeZoneByPhone($phone = ''){
        if(preg_match('/^996/', $phone)) return '+6';
        if(preg_match('/^998/', $phone)) return '+5';
        if(preg_match('/^374/', $phone)) return '+4';

        $geo_data = $this->getByPhone($phone);        

        $ans = [];

        if(isset($geo_data['code'])){
            if($geo_data['code']=='RU')
            {
                $int = (int) substr($phone, 1, 5);
                $city_codes = PhoneCodes::where('city_code', $int)->get();                                
                if(isset($city_codes[0]['time_zone'])) return str_replace('UTC','',$city_codes[0]['time_zone']);
                $ans = $this->dadataGet('phone',$phone);
                if(empty($ans['time_zone'])) return '';
                PhoneCodes::create($ans);
            }else if($geo_data['code']=='AZ'){
                    $ans['time_zone'] = 'UTC+3';
            }else if($geo_data['code']=='UA'){
                $ans['time_zone'] = 'UTC+2';
            }else if($geo_data['code']=='BY'){
                $ans['time_zone'] = 'UTC+3';
            }else if($geo_data['code']=='KZ'){
                $ans['time_zone'] = 'UTC+6';
            }else if($geo_data['code']=='KG'){
                $ans['time_zone'] = 'UTC+6';
            }else{
                $ans['time_zone'] = '';
            }
        }else{
            return '';
        }

        return str_replace('UTC','',$ans['time_zone']);
    } 

    //TEST

    /**
         * Проверка не рабочего времени у клиента
         * @param $time_zone - string
         * @param $queue - array
         * @return boolean
         */
    public function check_work_time_by_time_zone($queue = array(),$time_zone = NULL){   
        //  Загрузка диапазона рабочего времени
        $off_time_1 = @strtotime(@date('Y-m-d ').$queue['off_time1']);
        $off_time_2 = @strtotime(@date('Y-m-d ').$queue['off_time2']);
                            
        $next_day = @strtotime(@date('Y-m-d'))+86400;

        if(($next_day!==($off_time_1+86400)) && (($off_time_1-$off_time_2)<0)) $off_time_1 = $off_time_1+86400;
            
        //  Если есть часовой пояс, сравниваем с ним
        if(($time_zone!==NULL)&&!empty($time_zone))
        {
            $client_date = $this->parse_time_zone($time_zone);
            $client_date_str = strtotime(@date('Y-m-d').' '.@date('H:i:s',$client_date));
            if(($client_date_str>$off_time_1)||($client_date_str<$off_time_2)) $work_time = false; else $work_time = true;
            }else{
                if((time()>$off_time_1)||(time()<$off_time_2)) $work_time = false; else $work_time = true;
            }

        return $work_time;
    }

    /**
    * Парсинг часового пояса
    * @param $utc - string
    **/
    public function parse_time_zone($utc = ''){      
        if(stristr($utc, '+')) {
            $utcTime = intval(str_replace('+', '', $utc));
            $result = strtotime(gmdate("Y-m-d H:i:s")) + $utcTime*3600;
            return $result;
        }elseif(stristr($utc, '-')){
            $utcTime = intval(str_replace('-', '', $utc));
            $result = strtotime(gmdate("Y-m-d H:i:s")) - $utcTime*3600;
            return $result;
        } else{
            return strtotime(gmdate("Y-m-d H:i:s"));
        }
    }

    /**
    *   Получение списка рабочих зон относительно раб времени очереди
    */
    public function get_time_zone_by_work_time($queue = []){
        $time_zone_arr = [
            '1'=>'+1',
            '2'=>'+2',
            '3'=>'+3',
            '4'=>'+4',
            '5'=>'+5',
            '6'=>'+6',
            '7'=>'+7',
            '8'=>'+8',
            '9'=>'+9',
            '10'=>'+10',
            '11'=>'+11',
            '12'=>'+12',
            '13'=>'+13',
            '14'=>'+14',
            '15'=>'+15',
            '16'=>'+16',
            '17'=>'+17',
            '18'=>'+18'
        ];
        $work_zone = [];
        foreach ($time_zone_arr as $key => $value)
        {   
            if($this->check_work_time_by_time_zone($queue,$value)){
                $work_zone[$key] = $value;
            }
        }
        return $work_zone;
    } 

    protected function getSearchRepository()
    {
        return $this->geoRepository;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }
}