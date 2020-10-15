<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProductCreateRequest;
use App\Http\Requests\Api\V2\ProductUpdateRequest;
use App\Repositories\ProductsRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Http\Requests\Api\V2\SuggestRequest;
use App\Services\ProductsService;

class ProductsController extends Controller
{
    public function getList(SearchRequest $request, ProductsService $service)
    {
        // продукты с гео из запроса + гео не указан
        if(strpos($request['filter'], "geo.name_ru") !== false) {
            $custom_filter = $request['filter'];
            $filter_array = explode(',', $custom_filter);
            foreach ($filter_array as $key => $value) {
                if (strpos($value, "geo.name_ru") !== false) {
                    $filter_array[$key] = "[" . $filter_array[$key];                //добавляем скобку в начале условия
                    $query = $filter_array[$key + 2];
                    $geo = substr($query, 0, strpos($query, "]"));
                    $brackets = substr($query, strpos($query, "]"));
                    $null_geo = "],\"or\",[\"geo.name_ru\",\"=\",\"\"]".$brackets;  //добавляем скобки в конце условия
                    $filter_array[$key + 2] = $geo.$null_geo;                       //соединяем запрос с пустым гео
                    $request['filter'] = implode(',', $filter_array);
                    break;
                }
            }
        }

        //return $this->search($request, $service);
        if(isset($request['group'])){
            $result =  $service->dxSwitchGroup($request);
            if(isset($result))
                return $result;
        }

        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);

        $result = $service->dxSearch($request);

        return  $result;

        /*$page           = $request->get('page', 1);
        $perPage        = $request->get('per_page', 20);
        $sortKey        = $request->get('sort_key', 'id');
        $sortDirection  = $request->get('sort_direction', 'desc');

        $products = $productsRepository->search($page, $perPage, $sortKey, $sortDirection);

        return $products;*/
    }

    public function exToExcel(SearchRequest $request, ProductsService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  response()->file($service->exToExcel($request)); 
    }

    public function getById($id, ProductsRepository $productsRepository)
    {
        $products = $productsRepository->find($id);
        return $products;
    }

    public function create(ProductCreateRequest $request, ProductsService $productsService)
    {
        return $productsService->create($request->validated(), true);
    }

    public function getSuggest(SearchRequest $request, ProductsService $service)
    {
        
        if($request['key']=='new_order'){
            return $this->errorResponse('Ошибка заказа', 404, ['product_search'=>['Доступно после сохранения']]);
        }
        return $service->searchProducts($request);        

        /*$data       = $request->validated();
        
        $q          = array_get($data, 'q');
        $filters    = (array)array_get($data, 'filters');
        
        $filters['is_work'] = 1;        

        if(empty($q)) {
            return [];
        }

        $result['data'] = $service->searchProducts($q, $filters);

        return $result;*/

        // $models = $service->suggest($user, $q, 200, $filters);
        // $result['data'] = $service->suggestPermitted($q, $this->auth->user()['organization']['role_id']);

        
    }


    public function update($id, ProductUpdateRequest $request, ProductsService $productsService)
    {
        $product = $productsService->update($id, $request->validated(), true);

        return $product;
    }

    public function delete($id, ProductsService $productsService)
    {
        return $productsService->delete($id);
    }
}
