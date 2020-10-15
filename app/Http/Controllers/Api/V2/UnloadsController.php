<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\UnloadRequest;
use App\Http\Requests\Api\V2\UnloadAPIRequest;

use Illuminate\Http\Request;

use App\Repositories\UnloadsRepository;
use App\Repositories\OrganizationsRepository;

use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\UnloadsService;
use App\Services\AtsService;
use Spatie\ArrayToXml\ArrayToXml;

use Exception;

class UnloadsController extends Controller
{

    public function dxSearch(SearchRequest $request, UnloadsService $service){
        $this->can('menu.main.unloads');
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function getList(SearchRequest $request, UnloadsRepository $unloadsRepository)
    {

        $page           = $request->get('page', 1);
        $perPage        = $request->get('per_page', 20);
        $sortKey        = $request->get('sort_key', 'id');
        $sortDirection  = $request->get('sort_direction', 'asc');

        $unloads = $unloadsRepository->search($page, $perPage, $sortKey, $sortDirection);

        return $unloads;
    }

    public function getById($id, UnloadsService $service)
    {
        $unloads = $service->getById($id);

        return $unloads;
    }
/*
    public function create(UnloadRequest $request, UnloadsService $unloadsService)
    {
        try {
            $unloads = $unloadsService->create($request->validated());
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $unloads;
    } */

    public function update($id, UnloadRequest $request, UnloadsService $unloadsService)
    {
        $this->can('menu.main.unloads');

        $unloads = $unloadsService->update($id, $request->validated(), true);

        return $unloads;
    }

    public function delete($id, UnloadsRepository $repository)
    {
        return $repository->delete($id);
    }

    public function api(UnloadAPIRequest $request, UnloadsService $unloadsService)
    {
       // $this->sendMessageToTelegram(json_encode($request->all()), 'get');
        $this->logToFile($request->all(), 'get');

        $key        = $request->get('key', 0);
        $start      = $request->get('start', 0);
        $stop       = $request->get('stop', 100);
        $format     = $request->get('format', 'json');

        $unloads = $unloadsService->getOrdersByAPIKey($key , $start, $stop-$start);

        if(!is_array($unloads) && $unloads == false ){
            if($format == "xml"){
                $response['form']['result'] = 0;
                $response['form']['massage'] = "action not completed";
                $unloads =  ArrayToXml::convert($response,"response", true, 'UTF-8');
                return response($unloads, 200, ['Content-Type' => 'application/xml']);
            }else{
                $response['response']['form']['result'] = 0;
                $response['response']['form']['massage'] = "action not completed";
                return response(json_encode($response), 400, ['Content-Type' => 'application/json']);
            }
        }

        if($format=='xml'){
            $list = [];
            $i=0;
            foreach($unloads as $u){
                $list['item'.$i] = $u;
                $i++;
            }
            $response['form']['result'] = 1;
            $response['form']['massage'] = "action completed";
            $response['form']['content'] = $list;
            $unloads =  ArrayToXml::convert($response,"response", true, 'UTF-8');
            return response( $unloads, 200)->header('Content-Type', 'text/xml');
        }

        return $unloads;
    }

    private function sendMessageToTelegram($text,$method){
        //$url = 'https://api.telegram.org/bot760928979:AAEZGAmEGa2naOhtT5CqTAfDOTLpgEgAbug/sendMessage?chat_id=693408883&text='.$text;
        $url = 'https://api.telegram.org/bot760928979:AAEZGAmEGa2naOhtT5CqTAfDOTLpgEgAbug/sendMessage?chat_id=-336776145&text='.$text.' method '.$method;

         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, 0);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

         $response = curl_exec ($ch);
         $err = curl_error($ch);  //if you need
         curl_close ($ch);

    }

    private function logToFile($data, $method){
        $file_name = date('Y-m-d').'.txt';
        $strD = '======== '.date('Y-m-d H:i:s')." =========\n";
        $strD .= $method."\n";
        $strD .= (json_encode($data))."\n";                        

        $dir = base_path('storage/app/files/order_unloads');
        if (!file_exists($dir) && !is_dir($dir)) {
            if(!mkdir($dir, 0755, true)) {
                return ['logs'=>"Не удалось создать каталог $dir"];
            }
        }
        
        file_put_contents("$dir/$file_name", $strD, FILE_APPEND | LOCK_EX);
    }

    public function apiPOST(Request $request, UnloadsService $service, OrganizationsRepository $organizationsRepository)
    {
       // $this->sendMessageToTelegram(json_encode($request->all()),'post');
        $this->logToFile($request->all(), 'post');

        $key        = $request->get('key', 0);
        $format     = $request->get('format', 'xml');        
        $bodyContent = $request->getContent();        

        if($format=='xml'){
            $loop_count = 0;
            while(true){
                if($bodyContent[0] != '<'){
                    $bodyContent = substr($bodyContent, 1);
                }else{
                    break;
                }
                $loop_count++;
                if($loop_count>200)
                    break;
            }
     
            $xml = new \SimpleXMLElement($bodyContent);
            $json_string = json_encode($xml);
            $result_array = json_decode($json_string, TRUE);                    
        }

        if($format=='json'){
            $result_array['form'] = $request->get('orders', []);                    
            if(!is_array($result_array['form']))$result_array['form'] = json_decode($result_array['form'], true);                                
        }        
        $organization = $organizationsRepository->findAllBy('api_key', $key)->first();       

        if($organization==null){
          $result = false;
          if(AtsService::getByKey($key))$result = $service->setOrdersFromAPI($result_array['form'], null);
        }else{
          // $result = $service->setOrdersFromAPI($result_array['form']['content'], $organization['id']);
          $result = $service->setOrdersFromAPI($result_array['form'], $organization['id']);
        }

        if($format=="xml"){

            if($result){
                $response['form']['result'] = 1;
                $response['form']['massage'] = "action completed";
                if(isset($result['is_new']) &&  $result['is_new']){
                    $response['form']['content']['import'.$result['import_id']]['add_id'] = $result['import_id'];
                    $response['form']['content']['import'.$result['import_id']]['new_id'] = $result['id'];    
                }
            }else{
                $response['form']['result'] = 0;
                $response['form']['massage'] = "action not completed";
            }
                $result =  ArrayToXml::convert($response,"response", true, 'UTF-8');

            return response($result, 200, ['Content-Type' => 'application/xml']);
        }else{

            if($result){
                $response['response']['form']['result'] = 1;
                $response['response']['form']['massage'] = "action completed";
            }else
            {
                $response['response']['form']['result'] = 0;
                $response['response']['form']['massage'] = "action not completed";
            }
            return response(json_encode($response), 200, ['Content-Type' => 'application/json']);
        }
    }

    public function webinarAPI(Request $request, UnloadsService $service, OrganizationsRepository $organizationsRepository)
    {
        // $this->sendMessageToTelegram(json_encode($request->all()),'post');
        $this->logToFile($request->all(), 'post');
        $key = $request->get('key', 0);

        $organization = $organizationsRepository->findAllBy('api_key', $key)->first();       
        if($organization == null){
            return response(json_encode(["error"=>'Ключ доступа не верный!']), 422, ['Content-Type' => 'application/json']); 
        }

        $result = $service->webinarAPI($request, $organization['id']);

        return response(json_encode($result), 200, ['Content-Type' => 'application/json']); 
    }

    private function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_numeric($key) ){
            $key = 'item'.$key; //dealing with <0/>..<n/> issues
        }
        if( is_array($value) ) {
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}

}
