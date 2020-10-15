<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProductCategoryRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Repositories\ProductCategoryRepository;
use App\Services\ProductCategoryService;

class ProductCategoryController extends Controller
{
    protected $relations = ['organization'];

    public function getList(SearchRequest $request, ProductCategoryRepository $productCategoryRepository)
    {
        $page           = $request->get('page', 1);
        $perPage        = $request->get('per_page', 20);
        $sortKey        = $request->get('sort_key', 'id');
        $sortDirection  = $request->get('sort_direction', 'desc');

        $category = $productCategoryRepository->search($page, $perPage, $sortKey, $sortDirection);

        return $category;
    }

      public function dxProductCategory(SearchRequest $request, ProductCategoryService $service){
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }

    public function getById($id, ProductCategoryRepository $productCategoryRepository)
    {
        $category = $productCategoryRepository->with($this->relations)->findBy('id', $id);

        return $category;
    }

    public function create(ProductCategoryRequest $request, ProductCategoryService $productCategoryService)
    {
        return $productCategoryService->create($request->validated(), true);
    }

    public function update($id, ProductCategoryRequest $request, ProductCategoryService $productCategoryService)
    {
        return $productCategoryService->update($id, $request->validated(), true);
    }

    public function delete($id, ProductCategoryService $productCategoryService)
    {
        //Категории продуктов не будут удаляться
        //return $productCategoryService->delete($id);
    }
}
