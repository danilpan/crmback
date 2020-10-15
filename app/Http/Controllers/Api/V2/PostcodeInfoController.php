<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\PostcodeInfoRequest;
use App\Repositories\PostcodeInfoRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\PostcodeInfoService;
use App\Models\PostcodeInfo;
use App\Models\DeliveryType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Auth;
use DB;
use Elasticsearch\Client as ElasticClient;

class PostcodeInfoController extends Controller
{

    public function getList(SearchRequest $request, PostcodeInfoRepository $repository){
        
        $items = $repository->all();

        return $items;
    }

    public function getOne(SearchRequest $request, PostcodeInfoService $service){     

        $postcode_infos = $service->getPostcodeInfos($request);                            

        return [
            'total' => $postcode_infos->count(),
            'data' => $postcode_infos->all()          
        ];
    }

    public function create(PostcodeInfoRequest $request, PostcodeInfoRepository $repository, ElasticClient $elastic)
    {
        //$organization_id = Auth::user()->organization_id;                   

        $organization_id = 67;
        $delivery_types = DeliveryType::where(['organization_id' => $organization_id, 'is_work'=>'1'])->pluck('id')->toArray();              
        $filename = 'Postcode_info_'.$organization_id.'.'.$request->file('file')->getClientOriginalExtension();
        $path = $request->file('file')->storeAs(
            'files', $filename
        );

        $path = base_path('storage/app/' .$path);        
        
        $writer = new Xlsx();
        $arr = $writer->load($path);
        $ar = $arr->getActiveSheet()->toArray();
        /*foreach ($ar as $value) {
            print_r($value);
        }
        die();        */
        /*$inputFileType = PHPExcel_IOFactory::identify($path);
        $types = ['Excel2007','Excel5','Excel2003XML']; //принимаемые типы файлов
        if(!in_array($inputFileType,$types)){ //если тип не Excel, удаляем файл, выводим предупреждение                        
            unlink(storage_path('app/files/'.$filename));
            return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Недопустимый формат файла']);
        }   
        $objReader = PHPExcel_IOFactory::createReader($inputFileType); // создаем объект для чтения файла       
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($path); // загружаем данные файла в объект
        $ar = $objPHPExcel->getActiveSheet()->toArray();*/
        //return $ar;
        /*$add_data = $repository->findWhere(['delivery_type_id'=>$delivery_types])->sortBy('id');              
        $params['query']['bool']['must']['terms']['delivery_type_id'] = $delivery_types;
        $repository->deleteFromIndexbyQuery($params);     
        $repository->reindexByData($add_data); 
        return count($add_data);*/
/*$response = $elastic->get([
    'index' => 'postcode_infos',
    'type' => 'postcode_infos',
    'id' => '2049021'
]);
return $response;*/

        //$ar = $request['array'];
        $final_arr = [];
        $iter=1;        
        foreach($ar as $item){
            if($item[0] && $item[1]){
                $temp_arr = array();
                if(!is_numeric($item[0])) {
                    unset($ar);
                    return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Строка '.$iter.'. Индекс: недопустимое значение']);
                }
                if(!is_numeric($item[1])){
                    unset($ar);
                    return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Строка '.$iter.'. ID Доставки: недопустимое значение']);                
                }
                if(!in_array($item[1],$delivery_types)){
                    unset($ar);
                    return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Строка '.$iter.'. ID Доставки: неверный ID']);                
                }
                $temp_arr['postcode'] = $item[0];
                $temp_arr['delivery_type_id'] = $item[1];
                if(is_string($item[2])){
                    $temp_arr['comment'] = $item[2];
                }else{
                    unset($ar);
                    return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Строка '.$iter.'. Комментарий: недопустимое значение']);
                };
                if(is_numeric($item[3])){
                    $temp_arr['time'] = $item[3];
                }else{
                    unset($ar);
                    return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Строка '.$iter.'. Время доставки: недопустимое значение']);
                };
                if(is_numeric($item[4])){
                    $temp_arr['price'] = $item[4];
                }else{
                    unset($ar);
                    return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Строка '.$iter.'. Цена доставки: недопустимое значение']);
                };
                $final_arr[] = $temp_arr;
            } else {
                unset($ar);
                return $this->errorResponse('Ошибка в файле', 422, ['file'=>'Строка '.$iter.'. Не указан индекс или ID Доставки']);
            }            
            $iter++;            
        }      

        unset($ar);
        //удаление в elastic
        //$params['query']['bool']['must']['terms']['delivery_type_id'] = $delivery_types;
        //$repository->deleteFromIndexbyQuery($params);                   
        
        //удаление в базе
        DB::table('postcode_infos')->whereIn('delivery_type_id',$delivery_types)->delete();   
        //запись в базе
        $collection = collect($final_arr);   //turn data into collection
        $chunks = $collection->chunk(2000); //chunk into smaller pieces
        $chunks->toArray(); //convert chunk to array        
        //$chunks = array_chunk($final_arr, 2000);
        foreach($chunks as $chunk)
        {
            DB::table('postcode_infos')->insert($chunk->toArray());                   
            //DB::table('postcode_infos')->insert($chunk);                   
        }



        unset($final_arr);
        //добавление в elastic
        //$add_data = $repository->findWhere(['delivery_type_id'=>$delivery_types])->sortBy('id');              
        //$arr_chunks = $add_data->chunk(20000); //chunk into smaller pieces
        //$arr_chunks->toArray(); //convert chunk to array
        //$chunks = array_chunk($final_arr, 250);
        /*foreach($arr_chunks as $arr_chunk)
        {
            $repository->reindexByData($arr_chunk);            
        }*/
        //$repository->reindexByData($add_data);
        //unset($add_data);        
        //$item = $repository->reindex();
        //return $item;*/
        


        

        //return 'Сохранено';
        
        return response()->json([
            'message'   => 'Сохранено'
        ], 200);
    }

    public function reindex(PostcodeInfoRepository $repository){
        //$organization_id = Auth::user()->organization_id;             
        $organization_id = 67;
        $delivery_types = DeliveryType::where(['organization_id' => $organization_id, 'is_work'=>'1'])->pluck('id')->toArray();                
        $add_data = $repository->findWhere(['delivery_type_id'=>$delivery_types])->sortBy('id');              
        $params['query']['bool']['must']['terms']['delivery_type_id'] = $delivery_types;
        $repository->deleteFromIndexbyQuery($params);     
        $repository->reindexByData($add_data);
        return response()->json([
            'message'   => 'Проиндексировано'
        ], 200);
    }

    public function delete($id, PostcodeInfoRepository $repository)
    {
        return $repository->delete($id);
    }
}
