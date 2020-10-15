<?php
namespace App\Http\Controllers\Api\V2;

use App\Repositories\SmsProvidersRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\SmsProviderRequest;
use App\Repositories\SmsRepository;
use App\Services\OrganizationsService;
use Auth;

class SmsProvidersController extends Controller
{

    public function getList(SearchRequest $request, SmsProvidersRepository $repository){
        $smsProviders = $repository->all()->sortBy('id');;
        return $smsProviders;
    }

    public function getById($id, SmsProvidersRepository $repository)
    { 
        $smsProvider = $repository->find($id);                
        return $smsProvider;
    }

    public function create(SmsProviderRequest $request, SmsProvidersRepository $repository, OrganizationsService $organizationsService)
    {
        $data = $request->validated();
        $data['data'] = json_encode($data['data'],JSON_UNESCAPED_UNICODE);                

        $organization_id = $organizationsService->getMyCompany(Auth::user()->organization_id)->id;

        $data['organization_id'] = $organization_id;
        $smsProvider = $repository->create($data);
        return $smsProvider;
    }

    public function update($id, SmsProviderRequest $request, SmsProvidersRepository $repository)
    {            
       $data = $request->validated();
       $data['data'] = json_encode($data['data'],JSON_UNESCAPED_UNICODE);        
       $smsProvider = $repository->update($data, $id, "id");       
       return $smsProvider;
    }

    public function delete($id, SmsProvidersRepository $repository, SmsRepository $smsRepository)
    {
        $sms = $smsRepository->findAllBy('sms_provider_id', $id);

        if($sms->count() > 0)
            return $this->errorResponse('Есть смс', 429, ['keys'=>['Невозможно удалить. Уже есть смс с этим провайдером.']]);                

        return $repository->delete($id);
    }
}
