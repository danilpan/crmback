<?php
namespace App\Services;

use App\Models\LnkGeoProduct;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\LnkGeoProductRepository;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductsRepository;
use App\Repositories\OrdersRepository;
use App\Models\User;
use App\Services\RolesService;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Auth;

use App\Queries\PermissionQuery;
use App\Libraries\ExportToExcel;

class ProductsService extends Service
{
    protected $productsRepository;
    protected $permissionQuery;
    protected $exportToExcel;
    protected $ordersRepository;
    protected $rolesService;
    protected $productImageRepository;
    protected $lnkGeoProductRepository;    
     
        
        public function __construct(
            ProductsRepository $productsRepository,
            OrdersRepository $ordersRepository,
            RolesService $rolesService,
            PermissionQuery $permissionQuery,
            ProductImageRepository $productImageRepository,
            LnkGeoProductRepository $lnkGeoProductRepository,            
            ExportToExcel $exportToExcel
        )
    {
        $this->productsRepository = $productsRepository;
        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
        $this->ordersRepository = $ordersRepository;        
        $this->rolesService = $rolesService;
        $this->productImageRepository = $productImageRepository;
        $this->lnkGeoProductRepository = $lnkGeoProductRepository;
    }

    public function searchProducts($request){
        if(isset($request['key'])){
            $order_info = $this->ordersRepository->searchByParams(
                    ['match' => [
                        'key' => $request['key']]
                    ], 
                    ['key'=>'asc']
            )->toArray();
            $organization_id = $order_info[0]['organization_id'];
        }else{
            $organization_id = Auth::user()->organization_id;
        }

        $request = $this->dxAddPermissionsWithoutGeo($request, $organization_id);

        $result = $this->dxSearch($request);
        return $result;

        /*$orgz_arr = $this->rolesService->getArrChildOrgsByOrganizationId($order_info[0]['organization_id']);          

        if($q){
            $query['should'][]['term']['article'] = $q;
            $query['should'][]['wildcard']['name'] = '*'.$q.'*';                   
            $main['constant_score']['filter']['bool']['should'] = $query['should'];
        }                
        
        $filter['must']['bool']['should'][]['terms']['organization_id'] = $orgz_arr;        
        $main['constant_score']['filter']['bool']['must'] = $filter['must'];  

        
        return $this->productsRepository->searchByParams($main, ['id'=>'asc'], 1, 20, false);*/

    }
    
    public function create($data, $reindex = false)
    {
        $product = $this->productsRepository->create($data);
        $product_id = $product->id;

        if(isset($data['geo_ids'])){
            $this->add_geo($product_id, ($data['geo_ids']));
        }

        if(isset($data['product_images']) && !empty($data['product_images'])){
            $this->add_images($product_id, $data['product_images']);
        }

        if ($product) {
            if ($reindex) {
                $this->productsRepository->reindexModel($product, true);;
            }

            return $product;
        }

        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $product = null;

        if(!isset($data['geo_ids'])){
            $data['geo_ids'] = [];
        }

        $this->add_geo($id, ($data['geo_ids']));

        if(isset($data['product_images']) && !empty($data['product_images'])){
            $this->add_images($id, $data['product_images']);
        }

        if ($this->productsRepository->update($data, $id)) {
            $product = $this->productsRepository->find($id);
            if ($reindex) {
                $this->productsRepository->reindexModel($product, true);
            }
        }

        return $product;
    }

    public function add_geo($product_id, $geo_ids){
        $product_geos = Product::find($product_id)->geo()->sync($geo_ids);
    }

    public function add_images($product_id, $images){
        $images_ids = [];
        $product = $this->productsRepository->find($product_id);

        foreach($images as $image){
            //если картинка уже в базе
            if(isset($image['id']) && !empty($image['id'])){
                if(isset($image['is_main'])){
                    $this->unset_main_image($product_id);
                    $image_temp = ProductImage::find($image['id']);
                    $image_temp->update(['is_main'=>true]);
                }
                $images_ids[] = $image['id'];
            }
            //если картинки нет в базе
            else{
                if(!empty($image['image_upload'])) {
                    $image_temp = $this->upload_image($product_id, $image);
                    $images_ids[] = $image_temp->id;
                }
            }
        }
        //удаляем картинки, которые не пришли в реквесте
        $product->images()->whereNotIn('id', $images_ids)->delete();
        $this->productImageRepository->reindexModel($product, true);
    }

    public function upload_image($product_id, $image){
        $image_link = Storage::disk('public_uploads')->putFile("product_images/$product_id", $image['image_upload']);
        $model = new ProductImage();
        $model->product_id = $product_id;
        $model->image = $image_link;

        if(!empty($image['is_main'])) {
            if($image['is_main'] == true) {
                $this->unset_main_image($product_id);
            }
            $model->is_main = $image['is_main'];
        }
        $model->save();

        return $model;
    }

    public function unset_main_image($product_id)
    {
        $product_images = ProductImage::where('product_id', $product_id);
        $main_image = $product_images->where('is_main', true)->first();
        if($main_image) {
            $main_image->update(['is_main' => false]);
        }
    }

    public function delete(int $id)
    {
        $product = $this->productsRepository->find($id);
        if ($product) {
            $this->productsRepository->deleteFromIndex($product);

            return $this->productsRepository->delete($id);
        }

        return false;
    }

    protected function prepareModel($data, $repo)
    {
        if(isset($data['import_id'])) {
            $model  = $this->productsRepository->findAllBy('import_id', $data['import_id'])->first();
        }
        else {
            $model  = $this->productsRepository->find($data['id']);

            $data['import_id']  = $data['id'];
        }

        unset($data['id']);
        if($model) {
            throw new RuntimeException();
        }


        $model  = $repo->create($data);

        return $model;
    }

    protected function getSearchRepository()
    {
        return $this->productsRepository;
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
