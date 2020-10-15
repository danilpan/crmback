<?php
namespace App\Services;

use App\Repositories\AtsGroupRepository;
use App\Models\AtsGroup;
use App\Models\Organization;
use App\Models\User;
use RuntimeException;
use Auth;
use App\Queries\PermissionQuery;

class AtsGroupService extends Service
{
    protected $repository;
    protected $permissionQuery;
    protected $usersService;
    protected $orgService;
    
    public function __construct(AtsGroupRepository $repository, 
                                PermissionQuery $permissionQuery, 
                                UsersService $UsersService,
                                OrganizationsService $orgService)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
        $this->usersService = $UsersService;
        $this->orgService = $orgService;
    }
    
    public function index()
    {
        $list = $this->repository->all();
        return $list;
    }
    
    public function create($data, $reindex = false)
    {
        $ats_group = $this->repository->create($data);

        if ($ats_group) {
            if ($reindex) {
                $this->repository->reindexModel($ats_group, true);
            }
            return $ats_group;
        }
        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $ats_group = null;
        
        $data = $this->repository->update($data, $id);
        if ($data) {
            $ats_group = $this->repository->find($id);
            
            if ($reindex) {
                $this->repository->reindexModel($ats_group, true);
            }
        }
        
        return $data;
    }
    
    /**
     * Возвращает список групп АТС связанных с организацией и её дочерними
     * 
     * @method getListByOrganization
     * @param integer $org_id ID организации
     * @param SearchRequest $request
     * @return Collection
     */
    public function getListByOrganization($org_id, $request)
    {
        $orgs = $this->orgService->getOrganizations($org_id)->transform(function ($item) {
            return $item->id;
        })->toArray();
        
        $filter = '[';
        foreach ($orgs as $org) {
            if ($filter != '[') $filter .= ',"or",';
            $filter .= '["organizations.id","=",'.$org.']';
        }
        $filter .= ']';
        $request['filter'] = $filter;

        $data = $this->dxSearch($request);
        return $data;
    }
    
    /**
     * Проверяет принадлежит ли группа организации пользователя или одной 
     * из дочерних организщаций
     * 
     * Возвращает True, если принадлежит организации пользователя
     * 
     * Возвращает True, если принадлежит дочерней организации пользователя или 
     * организации доступной по правам роли, и ползователь имеет право 
     * редактировать группы дочерних организаций
     * 
     * Возвращает Null, если принадлежит дочерней организации пользователя или 
     * организации доступной по правам роли, но ползователь не имеет прав
     * редактировать группы дочерних организаций
     * 
     * Возвращает False, если не принадлежит организациям пользователя, 
     * включая дочерние и организации доступные по правам роли
     * 
     * @method userHasAccessToGroup
     * @param App\Models\AtsGroup $ats_group Экземпляр модели группа АТС
     * @param boolean $with_childrens Право редактировать группы дочерних организаций
     * @return mixed
     */
    public function userHasAccessToGroup($ats_group, $with_childrens)
    {
        // Организации группы
        $orgs = $ats_group->organizations()->get();
        
        // Организации пользователя
        // если $with_childrens == истина,
        // то выбираются организации включая дочерние
        $accessed_orgs = $this->userAccessedOrganizations($with_childrens);
        
        $have_access = $orgs->count() == 0;
        foreach ($orgs as $org) {
            if (in_array($org->id, $accessed_orgs)) {
                // Если хоть одна организация входит в список организаций пользователя
                // то доступ есть
                return true;
            } else {
                // Иначе перебираем родительские организации группы
                $pid = $org->parent_id;
                while ($pid) {
                    if (in_array($org->pid, $accessed_orgs)) {
                        // Если родительская организация группы входит в список организаций пользователя
                        if ($with_childrens) {
                            // и при этом есть доступ на редактирование групп дочерних организщаций
                            // то доступ есть
                            return true;
                        } else {
                            return null;
                        }                        
                    } 
                    $pid = Organization::find($pid)->parent_id;
                }
            }
        };
        return false;
    }
    
    /**
     * Возвращает массив содержащий ID организаций к которым пользователь имеет доступ
     * Если указан параметр $with_children, то возвращается организация 
     * пользователя, организации доступные по роли и все их дочерние, иначе
     * только организация пользователя
     * 
     * @method userAccessedOrganizations
     * @param boolean $with_children Выводить ли дочерние организации
     * @return array
     */
    public function userAccessedOrganizations($with_children = false)
    {
        return $this->permissionQuery->getAllAccessCompanyIDs(Auth::user()->organization_id, $with_children);
    }
    
    /**
     * Проверяет может ли пользователь редактировать группу АТС
     * Возвращает true, если может, иначе вернёт массив аргументов для errorResponse
     * 
     * @method canEdit
     * @param integer $ats_group_id ID группы АТС
     * @return mixed
     */
    public function canEdit($ats_group_id)
    {
        $ats_group = AtsGroup::find($ats_group_id);
        if (!$ats_group) return ['error'=>true, 'Не найдено', 404, ['ats_group'=>'Группы с ID '.$ats_group_id.' не существует']];
        
        $check = $this->usersService->can('menu.main.ats.edit', Auth::user()->organization_id);
        if(!$check) return ['error'=>true, 'Нет доступа', 403, ['ats_group'=>'Нет доступа']];
        
        $with_childrens = $this->usersService->can('menu.main.ats.child_company_group_edit', Auth::user()->organization_id);
        $has_access = $this->userHasAccessToGroup($ats_group, $with_childrens);        
        
        if ($has_access === false) {
            return ['error'=>true, 'Нет доступа', 403, ['ats_group'=>'Группа не принадлежит ни одной из ваших организщаций!']];
        } else if ($has_access === null){
            return ['error'=>true, 'Нет доступа', 403, ['ats_group'=>'Нельзя редактировать дочерние группы!']];
        }
        
        return ["error"=>false];
    }

    protected function getSearchRepository()
    {
        return $this->repository;
    }

    protected function addSearchConditions(User $user=null, array $filters=null)
    {
        return $filters;
    }

    protected function getPermissionQuery()
    {
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib()
    {
        return null;
    }
}