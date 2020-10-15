<?php
namespace App\Services;

use App\Repositories\OrganizationsRepository;
use App\Repositories\ProductCategoryRepository;
use App\Repositories\UsersRepository;
use Illuminate\Support\Facades\Auth;
use App\Queries\PermissionQuery;
use App\Models\User;

class ProductCategoryService extends Service
{
    protected $productCategoryRepository;
    protected $organizationsRepository;
    protected $usersRepository;
    protected $permissionQuery;

    public function __construct(
        ProductCategoryRepository $productCategoryRepository,
        OrganizationsRepository $organizationsRepository,
        UsersRepository $usersRepository, 
        PermissionQuery $permissionQuery
    ) {
        $this->productCategoryRepository = $productCategoryRepository;
        $this->organizationsRepository = $organizationsRepository;
        $this->usersRepository = $usersRepository;
        $this->permissionQuery = $permissionQuery;
    }

    public function create($data, $reindex = false)
    {
        $data = $this->addOrganizationId($data);
        $category = $this->productCategoryRepository->create($data);

        if ($category) {
            if ($reindex) {
                $this->productCategoryRepository->reindexModel($category, true);
            }

            return $category;
        }

        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $category = null;
        if ($this->productCategoryRepository->update($data, $id)) {
            $category = $this->productCategoryRepository->find($id);

            if ($reindex) {
                $this->productCategoryRepository->reindexModel($category, true);
            }
        }

        return $category;
    }

    public function delete(int $id)
    {
        $category = $this->productCategoryRepository->find($id);
        if ($category) {
            $this->productCategoryRepository->deleteFromIndex($category);

            return $this->productCategoryRepository->delete($id);
        }

        return false;
    }

    protected function addOrganizationId($data)
    {
        $user = $this->usersRepository->find(Auth::user()->id, ['organization_id']);
        $data['organization_id'] = $user->organization_id;

        return $data;
    }
    
    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getSearchRepository()
    {
        return $this->productCategoryRepository;
    }

    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    protected function getExportToExcelLib(){
        return null;
    }
}