<?php
namespace App\Services;

use App\Libraries\ExportToExcel;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Queries\PermissionQuery;
use App\Repositories\OrdersRepository;
use App\Repositories\SmsTemplatesRepository;
use Illuminate\Support\Facades\Auth;

class SmsTemplatesService extends Service
{
    protected $smsTemplatesRepository;
    protected $permissionQuery;
    protected $exportToExcel;
    protected $usersService;
    protected $ordersRepository;
    protected $rolesService;

    public function __construct(
        SmsTemplatesRepository $smsTemplatesRepository,
        RolesService $rolesService,
        PermissionQuery $permissionQuery,
        UsersService $usersService,
        OrdersRepository $ordersRepository,
        ExportToExcel $exportToExcel
    )
    {
        $this->smsTemplatesRepository = $smsTemplatesRepository;
        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
        $this->usersService = $usersService;
        $this->ordersRepository = $ordersRepository;
        $this->rolesService = $rolesService;
    }

    public function create($data, $reindex = false)
    {
        $sms_template = $this->smsTemplatesRepository->create($data);
        $organization_id = Auth::user()->organization_id;

        if(isset($data['organizations']))
        {
            $check = $this->usersService->can('menu.main.dictionaries.sms_templates.edit_other', $organization_id);
            if(!$check)
                $data['organizations'] = [$organization_id];
        }

        if($sms_template && isset($data['organizations']))
        {
            $this->attachOrganization($sms_template->id, $data['organizations']);
        }

        if ($sms_template) {
            if ($reindex) {
                $this->smsTemplatesRepository->reindexModel($sms_template, true);
            }

            return $sms_template;
        }

        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $sms_template = $this->smsTemplatesRepository->find($id);
        $organization_id = Auth::user()->organization_id;

        if(isset($data['organizations']))
        {
            $check = $this->usersService->can('menu.main.dictionaries.sms.sms_templates.edit_other', $organization_id);
            if(!$check)
                $data['organizations'] = [$organization_id];
        }

        if($sms_template && isset($data['organizations']))
        {
            $this->attachOrganization($id, $data['organizations']);
        }

        if ($this->smsTemplatesRepository->update($data, $id)) {
            $sms_template = $this->smsTemplatesRepository->find($id);
            if ($reindex) {
                $this->smsTemplatesRepository->reindexModel($sms_template, true);
            }
        }

        return $sms_template;
    }

    public function attachOrganization($template_id, $ids){
        SmsTemplate::find($template_id)->organizations()->sync($ids);
    }

    public function delete(int $id)
    {
        $product = $this->smsTemplatesRepository->find($id);
        $product->organizations()->delete();
        if ($product) {
            $this->smsTemplatesRepository->deleteFromIndex($product);

            return $this->smsTemplatesRepository->delete($id);
        }

        return false;
    }

    public function getByOrderKey($key){
        $organization_id = Auth::user()->organization_id;

        $order_info = $this->ordersRepository->searchByParams(
            ['match' => [
                'key' => $key]
            ],
            ['key'=>'asc']
        )->toArray();

        $order = $order_info[0];
        $client_name = isset($order['client_name']) ? $order['client_name'] : null;
        $track_number = isset($order['track_number']) ? $order['track_number'] : null;
        $project = $order['projects'];
        $site = null;

        if ($project) {
            $project = $project[0]['name_for_client'];
        } else{
            $project = null;
        }
        if($order['project_page']) {
            $site = $order['project_page'][0]['link'];
        }elseif($project){
            if($order['projects'][0]['import_id']) {
                $import_id = explode('reklpro_id_', $order['projects'][0]['import_id'])[1];
                $json = json_decode(file_get_contents("https://rekl.pro/api_get_offers/get_offer_info?key=asdjkijn34nn88234bjjngjll33356n&offer_id=$import_id"), true);
                $site = $json['url'];
            }
        }

        $templates = SmsTemplate::where('is_work', true)
            ->whereHas('organizations', function ($query) use ($organization_id){
                $query->where('organization_id',$organization_id);
        });

        if(!$client_name){
            $templates = $templates->where('sms_text', 'not like', '%{{client_name}}%');
        }
        if(!$track_number){
            $templates = $templates->where('sms_text', 'not like', '%{{track_number}}%');
        }
        if(!$site){
            $templates = $templates->where('sms_text', 'not like', '%{{site}}%');
        }
        if(!$project){
            $templates = $templates->where('sms_text', 'not like', '%{{project}}%');
        }

        $templates = $templates->get()->makeHidden(['is_work', 'sms_text']);

        return response()->json($templates);
    }

    protected function getSearchRepository()
    {
        return $this->smsTemplatesRepository;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return $this->exportToExcel;
    }
}
