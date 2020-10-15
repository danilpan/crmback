<?php
namespace App\Http\Controllers\Api\V2;

use App\Repositories\BlackListRepository;
use App\Http\Requests\Api\V2\BlackListRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\BlackList;
use App\Models\LnkRoleEntityParam;
use App\Services\UsersService;
use App\Services\BlackListService;
use App\Services\GetAddressService;

use Auth;

class BlackListController extends Controller
{    

    public function create(BlackListRequest $request, BlackListService $service, UsersService $usersService)
    {
       
        /*$entity = new LnkRoleEntityParam();
        $entity->role_id = 7;
        $entity->entity_param_id = 26;
        $entity->entity_id = 1;
        $entity->save();*/          

        $check = $usersService->can('menu.main.order.blacklist', Auth::user()->organization_id);
        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['blacklist'=>'Нет доступа']);

        return $service->create($request);       

    } 

    public function delete($id, BlackListRepository $repository, BlackListService $service, UsersService $usersService)
    {

        $check = $usersService->can('menu.main.order.blacklist', Auth::user()->organization_id);
        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['blacklist'=>'Нет доступа']);

        return $service->delete($id);

    }


}
