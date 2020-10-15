<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\SmsTemplateRequest;
use App\Models\SmsTemplate;
use App\Repositories\SmsTemplatesRepository;
use App\Services\SmsTemplatesService;
use App\Services\UsersService;

class SmsTemplatesController extends Controller
{
    public function getList(SearchRequest $request, SmsTemplatesService $service, UsersService $usersService)
    {
        $organization_id = $this->auth->user()['organization_id'];
        $check = $usersService->can('menu.main.dictionaries.sms_templates.view', $organization_id);
        $request = $service->dxAddPermissions($request, $organization_id);
        $request['take'] = 1000;

        if(!$check)
            $request['filter'] = "[[\"organizations.id\", \"=\", \"$organization_id\"]]";

        $result = $service->dxSearch($request);

        return  $result;

    }

    public function getByOrderKey($id, SmsTemplatesService $service)
    {
        $data = $service->getByOrderKey($id);

        return $data;
    }

    public function exToExcel(SearchRequest $request, SmsTemplatesService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);

        return  response()->file($service->exToExcel($request));
    }

    public function getById($id, SmsTemplatesRepository $repository)
    {
        $sms_template = $repository->find($id);

        return $sms_template;
    }

    public function create(SmsTemplateRequest $request, SmsTemplatesService $service, UsersService $usersService)
    {
        $organization_id = $this->auth->user()['organization_id'];
        $check = $usersService->can('menu.main.dictionaries.senders.create', $organization_id);

        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['sms_templates'=>'Нет доступа на cоздание.']);

        return $service->create($request->validated(), true);
    }

    public function update($id, SmsTemplateRequest $request, SmsTemplatesService $service, UsersService $usersService)
    {
        $organization_id = $this->auth->user()['organization_id'];
        $check = $usersService->can('menu.main.dictionaries.sms.sms_templates.edit', $organization_id);

        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['sms_templates'=>'Нет доступа на обновление.']);

        $sms_template = $service->update($id, $request->validated(), true);

        return $sms_template;
    }

    public function delete($id, SmsTemplatesService $service)
    {
        return $service->delete($id);
    }
}
