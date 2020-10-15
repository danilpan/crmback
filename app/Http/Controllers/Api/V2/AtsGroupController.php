<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\AtsGroup;
use App\Models\Organization;
use App\Http\Requests\Api\V2\AtsGroupRequest;
use App\Http\Requests\Api\V2\AtsGroupUpdateRequest;
use App\Http\Requests\Api\V2\AtsGroupAttachOrganizationsRequest;
use App\Repositories\AtsGroupRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\AtsGroupService;
use App\Services\UsersService;
use Auth;

class AtsGroupController extends Controller
{
    protected $service;
    protected $usersService;
    
    public function __construct(AtsGroupService $service, UsersService $usersService)
    {
        $this->service = $service;
        $this->userService = $usersService;
    }
    
    /**
     * Возвращает список всех доступных пользователю групп АТС, включая группы 
     * дочерних компаний и доступных по правам роли, если соответствующие права есть
     * 
     * @method index
     * @param string $force 
     * @return Collection
     */
    public function index($force = null, SearchRequest $request, UsersService $usersService, AtsGroupService $service)
    {
        if ($force == 'f') {
            return $service->index();
        }
        
        $view_children = $usersService->can("menu.main.ats.child_company_group_view", Auth::user()->organization_id) ||
                         $usersService->can("menu.main.ats.child_company_group_edit", Auth::user()->organization_id);        
        $accessed_orgs = $service->userAccessedOrganizations($view_children);

        $filter = '[';
        foreach ($accessed_orgs as $org) {
            if ($filter != '[') $filter .= ',"or",';
            $filter .= '["organizations.id","=",'.$org.']';
        }
        $filter .= ']';
        if ($request['is_work'] == 'true') {
            $filter = '[' . $filter . ',"and",["is_work","=","true"]]';
        }
        $request['filter'] = $filter;

        $data = $service->dxSearch($request);

        return $data;
    }

    /**
     * Создает новую запись в таблице AtsGroup
     */
    public function store(AtsGroupRequest $request, AtsGroupService $service, UsersService $usersService, AtsGroupRepository $repository)
    {
        $check = $usersService->can('menu.main.ats.create', Auth::user()->organization_id);
        if(!$check) return $this->errorResponse('Нет доступа', 403, ['ats_group'=>'Нет доступа']);
        
        $data = $request->validated();
        
        $organization_id = isset($data['organization_id']) ? $data['organization_id'] : Auth::user()->organization_id;
        $with_children = $usersService->can("menu.main.ats.child_company_group_edit", Auth::user()->organization_id); 
        $accessed_orgs = $service->userAccessedOrganizations($with_children);
        if (!in_array($organization_id, $accessed_orgs)) {
            return $this->errorResponse('Нет доступа', 403, ['organization_id'=>'Нет доступа к организации']);
        }

        $ats_group = $service->create($data, true);
        $ats_group->organizations()->attach($organization_id);
        $ats_group = $repository->find($ats_group->id);
        $repository->reindexModel($ats_group, true);
        return $ats_group;
    }

    /**
     * Возвращает AtsGroup по ID
     */
    public function show($id, $force = null, AtsGroupRepository $ats_groupRepository, UsersService $usersService, AtsGroupService $service)
    {        
        $ats_group = $ats_groupRepository->find($id);
        if (!$ats_group) {
            return $this->errorResponse('Не найдено', 404, ['organization'=>'Группы АТС с ID '.$id.' не существует']);
        }
        
        if ($force == 'f'){
            return $ats_group;
        }
        
        $view_children = $usersService->can("menu.main.ats.child_company_group_view", Auth::user()->organization_id) ||        
                         $usersService->can("menu.main.ats.child_company_group_edit", Auth::user()->organization_id);        
        $accessed_orgs = $service->userAccessedOrganizations($view_children);
        
        $ats_group = $ats_groupRepository->find($id);
        $ats_group_orgs = $ats_group->organizations->map(function ($item) {
            return $item->id;
        })->toArray();
        
        if (empty(array_intersect($ats_group_orgs, $accessed_orgs))) {
            return $this->errorResponse('Нет доступа', 403, ['organization_id'=>'Нет доступа к группе АТС']);
        }
        
        return $ats_group;
    }

    /**
     * Редактирует AtsGroup по ID
     */
    public function update($id, AtsGroupUpdateRequest $request, AtsGroupService $service)
    {        
        $ats_group = AtsGroup::find($id);
        
        $result = $service->canEdit($id);
        if ($result['error'] === true) {
            return $this->errorResponse($result[0], $result[1], $result[2]);
        }
        
        $data = $request->validated();
        $ats_group = $service->update($id, $data, true);
        return $ats_group;
    }

    /**
     * Удаляет AtsGroup по ID
     */
    public function destroy($id, AtsGroupRepository $repository, AtsGroupService $service)
    {
        $result = $service->canEdit($id);
        if ($result['error'] === true) {
            return $this->errorResponse($result[0], $result[1], $result[2]);
        }
        $ats_group = $repository->find($id);
        $repository->deleteFromIndex($ats_group);
        $destroyed = $repository->delete($id);
        return $destroyed;
    }
    
    /**
     * Возвращает список AtsGroup принадлежащих организации
     * Если ID не указан, то используется организация пользователя
     * Также добавляет группы дочерних компаний и доступных по правам роли, 
     * если соответствующие права есть
     * 
     * @method getListByOrganization
     * @param integer $org_id ID организации
     * @return Collection
     */
    public function getListByOrganization($org_id=null, SearchRequest $request, UsersService $usersService, AtsGroupService $service)
    {
        if ($org_id == null || $org_id == 0) {
            $org_id = Auth::user()->organization_id;
        }
        
        $org = Organization::find($org_id);
        if (!$org) {
            return $this->errorResponse('Не найдено', 404, ['organization'=>'Организации с ID '.$org_id.' не существует']);
        }
        
        $view_children = $usersService->can("menu.main.ats.child_company_group_view", Auth::user()->organization_id) ||        
                         $usersService->can("menu.main.ats.child_company_group_edit", Auth::user()->organization_id);        
        $accessed_orgs = $service->userAccessedOrganizations($view_children);
        
        if (!in_array($org_id, $accessed_orgs)) {
            return $this->errorResponse('Нет доступа', 403, ['organization_id'=>'Нет доступа к организации']);
        }
        
        if ($view_children) {
            $ats_groups = $service->getListByOrganization($org_id, $request);
        } else {
            $request['filter'] = '[["organizations.id","=",'.$org_id.']]';
            $ats_groups = $service->dxSearch($request);
        }

        
        // return ["data" => $ats_groups];
        return $ats_groups;
    }

    /**
     * Связывает группу АТС с организацией или организациями
     * в реквесте по ключу 'organizations' передаётся массив, содержащий
     * ID организаций, с которыми будет связана группа
     * Организации не включённые в данный массив будут отвязаны
     * 
     * @method attachOrganizations
     * @param integer $id ID группы АТС
     * @param AtsGroupAttachOrganizationsRequest $request
     * @param AtsGroupService $service
     * @param UsersService $usersService
     * @return AtsGroup
     */
    public function attachOrganizations($id, 
                                        AtsGroupAttachOrganizationsRequest $request, 
                                        AtsGroupService $service, 
                                        UsersService $usersService, 
                                        AtsGroupRepository $repository)
    {        
        $result = $service->canEdit($id);
        if ($result['error'] === true) {
            return $this->errorResponse($result[0], $result[1], $result[2]);
        }
        
        $can_attach = $usersService->can("menu.main.ats.edit_to_company_binding", Auth::user()->organization_id);  
        if (!$can_attach) {
            return $this->errorResponse('Нет доступа', 403, ['ats_bind_company'=>'Вы не можете редактировать привязку к организации']);
        }
        
        $ats_group = $repository->find($id);
        
        $data = $request->validated();

        $edit_children = $usersService->can("menu.main.ats.child_company_group_edit", Auth::user()->organization_id);  
        // Организации к которым я имею доступ      
        $accessed_orgs = $service->userAccessedOrganizations($edit_children);
        
        // Организации которые сейчас связаны с группой
        $ats_group_orgs = $ats_group->organizations->map(function ($item) {
            return $item->id;
        })->toArray();
        
        // Переданные организации которые я могу изменить 
        $allowed_orgs = array_intersect($data['organizations'], $accessed_orgs);
        
        // Организации группы которые мне запрещено изменять
        $forbidden_orgs_in_ats_group = array_diff($ats_group_orgs, $accessed_orgs);
        
        // С группой будут связаны переданные организации, которые я могу изменять 
        // плюс те, которые уже были и мне запрещено их изменять
        $to_be_attached = array_merge($allowed_orgs, $forbidden_orgs_in_ats_group);
        
        if (count($to_be_attached) == 0) {
            return $this->errorResponse('Нет доступа', 403, ['ats_bind_company'=>'После данной операции с группой не будет связана ни одна организация']);
        }
        
        $ats_group->organizations()->sync($to_be_attached);
        $ats_group = $repository->find($id);
        $repository->reindexModel($ats_group, true);
        
        return AtsGroup::find($id);
    }
    
    /**
     * Возвращает организации, которые связаны с группой
     * 
     * @method getOrganizations
     * @param integer $id ID группы АТС
     * @param AtsGroupService
     * @return Collection
     */
    public function getOrganizations($id, AtsGroupService $service)
    {
        $ats_group = AtsGroup::find($id);
        if (!$ats_group) {
            return $this->errorResponse('Не найдено', 404, ['ats_group'=>'Группы с ID '.$ats_group_id.' не существует']);
        }
        
        // return ["data" => $ats_group->organizations()->get()];
        return $ats_group->organizations()->get();
    }
}
