<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\OrderCreate;
use App\Services\OrdersService;
use App\Repositories\OrdersRepository;

use App\Http\Resources\V1\Order as OrderResource;

class OrdersController extends Controller
{
    public function getList()
    {
        return [];
    }

    public function getById($id, OrdersRepository $ordersRepository)
    {
        $order      = $ordersRepository->with(['project', 'site', 'gasket'])->find($id);
        $resource   = new OrderResource($order);

        return $resource;
    }

    public function create(OrderCreate $request, OrdersService $ordersService)
    {
        $orderData  = $request->only([
            'id',
            'age_id',
            'import_id',
            'phone',
            'site_product_name',
            'id_webmaster',
            'id_webmaster_transit',
            'site_product_price',
            'dop_info',
            'id_transit',
            'country_code',
            'webmaster_type',
            'profit',
            'real_profit',
            'request_hash'
        ]);
        $orderData  = [
            'id'                    => $orderData['id'] ?? null,
            'age_id'                => $orderData['age_id'] ?? null,
            'import_id'             => $orderData['import_id'] ?? null,
            'phone'                 => $orderData['phone'] ?? null,
            'site_product_name'     => $orderData['site_product_name'] ?? null,
            'webmaster_id'          => $orderData['id_webmaster'] ?? null,
            'webmaster_transit_id'  => $orderData['id_webmaster_transit'] ?? null,
            'site_product_price'    => $orderData['site_product_price'] ?? null,
            'description'           => $orderData['dop_info'] ?? null,
            'transit_id'            => $orderData['id_transit'] ?? null,
            'country_code'          => $orderData['country_code'] ?? null,
            'webmaster_type'        => $orderData['webmaster_type'] ?? null,
            'profit'                => $orderData['profit'] ?? null,
            'real_profit'           => $orderData['real_profit'] ?? null,
            'request_hash'          => $orderData['request_hash'] ?? null,
            'type'                  => 'api'
        ];

        $projectData = $request->get('project');

        if (!empty($projectData)) {
            $projectData = [
                'id'        => (isset($projectData['id']) && (int)$projectData['id']) ? $projectData['id'] : null,
                'import_id'        => $projectData['import_id'] ?? null,
                'name_for_client'        => $projectData['name_for_client'] ?? null,
                'desc'        => $projectData['desc'] ?? null,
                'is_work'        => $projectData['is_work'] ?? null,
                'hold'        => $projectData['hold'] ?? null,
                'name'        => $projectData['name'] ?? null,
                'sex'        => $projectData['sex'] ?? null,
                'countries'        => $projectData['countries'] ?? null,
                'prognos'        => $projectData['prognos'] ?? null,
                'parent_id'        => $projectData['parent_id'] ?? null
            ];
        }

        $gasketData = $request->get('id_gasket');

        if(!empty($gasketData)) {
            $gasketData = [
                'id'            => (isset($gasketData['id']) && (int)$gasketData['id']) ? $gasketData['id'] : null,
                'name'          => $gasketData['name'] ?? null,
                'url'           => $gasketData['url'] ?? null,
                'id_product'    => $gasketData['offer_id'] ?? null,
                'cr'            => $gasketData['cr'] ?? null
            ];
        }


        $siteData   = $request->get('site');
        if(!empty($siteData)) {
            $siteData   = [
                'id'                => $siteData['id'] ?? null,
                'import_id'         => $siteData['import_id'] ?? null,
                'title'             => $siteData['name'] ?? null,
                'project_id'        => $siteData['import_project_id'] ?? null,
                'url'               => $siteData['url'] ?? null
            ];
        }


//        dd($orderData, $projectData, $gasketData, $siteData);

        /*$saleData = $request->get('project', [
        	'id',
            'uniqued_import_id',
            'id_lead',
            'id_product',
            'id_lead'
            'name',
            'comment',
            'price',
            'cost_price',
            'price_prime',
            'is_additional',
            'is_additional_lvl_2',
            'upsale_lvl_1',
            'upsale_lvl_2',
            'autor_additional',
            'weight',
            'quantity',
            'quantity_price'
        ]);
       
        $saleData = [
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null,
            'id'        => $projectData['id'] ?? null
        ];*/

        $order  = $ordersService->create($orderData, $gasketData, $projectData, $siteData);

        return $order;
    }

    public function update($id)
    {
        return $id;
    }

    public function delete($id)
    {
        return $id;
    }
}