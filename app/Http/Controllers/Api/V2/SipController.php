<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Sip;
use App\Models\EntityParam;
use App\Http\Requests\Api\V2\SipRequest;
use App\Repositories\SipRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\SipService;
use App\Services\UsersService;
use Auth;

class SipController extends Controller
{
    protected $permissions;
    protected $usersService;
    protected $user;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.ats.trunks.$type",Auth::user()->organization_id);
    }
    
    /**
     * Возвращает список всех Sip
     */
    public function index(SearchRequest $request, SipService $service)
    {
        if ($this->can('view')) {
            if ($this->can("child_view")) {
                $role_id = Auth::user()->organization()->first()->role_id;
                $org_id = Auth::user()->organization_id;
                $orgs_by_role = $service->organizationsList($this->can("child_view"));
                $filter = '[["ats_group.organizations.id","=",'.$org_id.']';
                foreach ($orgs_by_role as $org) {
                    $filter .= ',"or",["ats_group.organizations.id","=",'.$org.']';
                }
                $filter .= ']';
            } else {
                $org_id = Auth::user()->organization_id;
                $filter = '["ats_group.organizations.id","=",'.$org_id.']';
            }
            foreach ($request->toArray() as $key => $value) {
                switch ($key) {
                    case 'is_work':
                        if ($value == 'true') {
                            $filter .= ',"and",["is_work","=","true"]';
                        }
                        break;
                        
                    case 'take':
                    case 'skip':
                        break;
                    
                    default:
                        $filter .= ',"and",["' . $key . '","=","' . $value . '"]';
                        break;
                }
            }
            $filter = '[' . $filter . ']';
            // $filter = '[[["ats_group.organizations.id","=",163],"or",["ats_group.organizations.id","=",163]],"and",["is_work","=","true"]]';
            // return $filter;
            // echo "\n$filter\n";
            // preg_match_all("/\[[^\[\]]*\]/", $filter, $arr);
            // print_r($arr);
            // return;
            $request['filter'] = $filter;
            $data = $service->dxSearch($request);
            return $data;
        } else {
            return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Вы не можете просматривать транки']);
        }           
    }

    /**
     * Создает новую запись в таблице Sip
     */
    public function store(SipRequest $request, SipService $service)
    {
        if (!$this->can('create')) {
            return $this->errorResponse('Нет доступа', 403, ['tranks_create' => 'Вы не можете создавать транки']);
        }
        $data = $request->validated();
        $sip = $service->create($data, true);
        return $sip;
    }

    /**
     * Возвращает Sip по ID
     */
    public function show($id, SipRepository $sipRepository, SipService $service)
    {
        $sip = $sipRepository->find($id);
        if (!$sip) {
            return $this->errorResponse('Не найдено', 404, ['tranks_edit'=>'Транка с ID '.$id.' не существует']);
        }
        if ($this->can('view')) {
            $orgs = $sip->atsGroup->organizations->pluck("id")->toArray();
            if ($this->can("child_view")) {
                $role_id = Auth::user()->organization()->first()->role_id;
                $org_id = Auth::user()->organization_id;
                $orgs_by_role = $service->organizationsList($this->can("child_view"));
                $allowed_orgs = array_intersect($orgs, $orgs_by_role);
                if (count($allowed_orgs) == 0) {
                    return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Транк не принадлежит ни одной из ваших организаций']);
                }
            } elseif(!in_array(Auth::user()->organization_id, $orgs)) {
                return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Транк не принадлежит вашей организации']);
            }
        } else {
            return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Вы не можете просматривать транки']);
        }
        
        return $sip;
    }

    /**
     * Редактирует Sip по ID
     */
    public function update($id, SipRequest $request, SipService $service)
    {
        $sip = Sip::find($id);
        if (!$sip) {
            return $this->errorResponse('Не найдено', 404, ['tranks_edit'=>'Транка с ID '.$id.' не существует']);
        }
        if (!$this->can('edit')) {
            return $this->errorResponse('Нет доступа', 403, ['tranks_edit' => 'Вы не можете редактировать транки']);
        }
        $orgs = $sip->atsGroup->organizations->pluck("id")->toArray();
        if (!$this->can("child_edit") && !in_array(Auth::user()->organization_id, $orgs)) {
            return $this->errorResponse('Нет доступа', 403, ['tranks_edit' => 'Вы можете редактировать транки только своей организации']);
        }
        $role_id = Auth::user()->organization()->first()->role_id;
        $org_id = Auth::user()->organization_id;
        $orgs_by_role = $service->organizationsList($this->can("child_edit"));
        $allowed_orgs = array_intersect($orgs, $orgs_by_role);
        if (count($allowed_orgs) == 0) {
            return $this->errorResponse('Нет доступа', 403, ['tranks_edit' => 'Транк не принадлежит ни одной из ваших организаций']);
        }
        $data = $request->validated();
        $sip = $service->update($id, $data, true);
        return $sip;
    }

    /**
     * Удаляет Sip по ID
     */
    public function destroy($id, SipRepository $repository, SipService $service)
    {
        $sip = $repository->find($id);
        if (!$sip) {
            return $this->errorResponse('Не найдено', 404, ['tranks_delete'=>'Транка с ID '.$id.' не существует']);
        }
        if (!$this->can('edit')) {
            return $this->errorResponse('Нет доступа', 403, ['tranks_delete' => 'Вы не можете удалять транки']);
        }
        $orgs = $sip->atsGroup->organizations->pluck("id")->toArray();
        if (!$this->can("child_edit") && !in_array(Auth::user()->organization_id, $orgs)) {
            return $this->errorResponse('Нет доступа', 403, ['tranks_delete' => 'Вы можете удалять транки только своей организации']);
        }
        $role_id = Auth::user()->organization()->first()->role_id;
        $org_id = Auth::user()->organization_id;
        $orgs_by_role = $service->organizationsList($this->can("child_edit"));
        $allowed_orgs = array_intersect($orgs, $orgs_by_role);
        if (count($allowed_orgs) == 0) {
            return $this->errorResponse('Нет доступа', 403, ['tranks_delete' => 'Транк не принадлежит ни одной из ваших организаций']);
        }
        $repository->deleteFromIndex($sip);
        return $repository->delete($id);
    }
    
    /**
     * Возвращает каллер айди транков, которые доступны пользователю
     */
    public function allCallerIds(SearchRequest $request, SipRepository $repository, SipService $service)
    {
        if ($this->can('view')) {
            $with_children = false;
            if ($this->can("child_view")) {
                $with_children = true;
            }
            $data["data"] = $service->getAllAccessCallerIDs($with_children, false, $request->toArray());
            return $data;
        } else {
            return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Вы не можете просматривать транки']);
        }
    }
    
    public function callerIds($id, SipRepository $repository, SipService $service)
    {
        $sip = $repository->find($id);
        if (!$sip) {
            return $this->errorResponse('Не найдено', 404, ['tranks_edit'=>'Транка с ID '.$id.' не существует']);
        }
        if ($this->can('view')) {
            $orgs = $sip->atsGroup->organizations->pluck("id")->toArray();
            if ($this->can("child_view")) {
                $role_id = Auth::user()->organization()->first()->role_id;
                $org_id = Auth::user()->organization_id;
                $orgs_by_role = $service->organizationsList($this->can("child_view"));
                $allowed_orgs = array_intersect($orgs, $orgs_by_role);
                if (count($allowed_orgs) == 0) {
                    return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Транк не принадлежит ни одной из ваших организаций']);
                }
            } elseif(!in_array(Auth::user()->organization_id, $orgs)) {
                return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Транк не принадлежит вашей организации']);
            }
        } else {
            return $this->errorResponse('Нет доступа', 403, ['tranks_view' => 'Вы не можете просматривать транки']);
        }
        
        return $sip->sipCallerIds()->get();
    }
}
