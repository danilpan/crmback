<?php
namespace App\Http\Controllers\Api\V2;

use App\Repositories\SmsRulesRepository;
use App\Repositories\SmsProvidersRepository;
use App\Http\Requests\Api\V2\SmsRuleUpdateRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\SmsRule;
use Auth;

class SmsRulesController extends Controller
{

    public function getList(SearchRequest $request, SmsRulesRepository $repository){
        $smsRules = $repository->all();
        return $smsRules;
    }

    public function getById($id, SmsRulesRepository $repository)
    {
        //smsRule = $repository->find($id);                
        $smsRules = $repository->findAllBy('sms_provider_id', $id)->sortBy('id');

        return $smsRules;
    }

    public function create(SearchRequest $request, SmsRulesRepository $repository, SmsProvidersRepository $SmsProvidersRepository)
    {
     
        $data = $request->toArray();
        $SmsProvider = $SmsProvidersRepository->find($data['sms_provider_id']);  
        $data['organization_id'] = $SmsProvider['organization_id'];               
        
        $check = $data;        

        unset($check['type']);
        unset($check['sms_provider_id']);
        $smsRules = SmsRule::where($check)->get();       
        foreach ($smsRules as $rule) {
            if($rule['geo_id']==$data['geo_id']){
                if($rule['type']==$data['type'])
                    return $this->errorResponse('Тип отправки', 422, ['type'=>['Значение существует']]);
            }
            if(is_null($rule['type'])){
                return $this->errorResponse('Тип отправки', 422, ['type'=>['Значение существует']]);
            }elseif(is_null($data['type'])){
                return $this->errorResponse('Тип отправки', 422, ['type'=>['Значение существует']]);
            }

        }              
        $smsRule = SmsRule::firstOrCreate($data);        
        return $smsRule;
    }

    public function update($id, SmsRuleUpdateRequest $request, SmsRulesRepository $repository)
    {            
       
       $data = $request->validated();              
       
       $smsRule = $repository->update($data, $id, "id");       
       return $smsRule;
    }

    public function delete($id, SmsRulesRepository $repository)
    {
        return $repository->delete($id);
    }
}
