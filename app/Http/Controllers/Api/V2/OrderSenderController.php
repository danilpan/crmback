<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\OrderSenderRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\OrderSender;
use App\Repositories\OrderSenderRepository;
use App\Services\OrderSenderService;
use App\Services\UsersService;
use Auth;

class OrderSenderController extends Controller
{
    public function getList(SearchRequest $request, OrderSenderService $service, UsersService $usersService)
    {
        $check = $usersService->can('menu.main.dictionaries.senders.view', Auth::user()->organization_id);

        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['order_sender'=>'Нет доступа на просмотр этой страницы.']);

        $request = $service->dxAddPermissions($request, $this->auth->user()->organization_id);

        return $service->dxSearch($request);
    }

    public function getById($id, OrderSenderRepository $repository, UsersService $usersService)
    {
        $check = $usersService->can('menu.main.dictionaries.senders.edit', Auth::user()->organization_id);

        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['order_sender'=>'Нет доступа на просмотр этой страницы.']);

        $order_sender = $repository->find($id);

        return $order_sender;
    }

    public function create(OrderSenderRequest $request, OrderSenderService $service, UsersService $usersService)
    {
        $check = $usersService->can('menu.main.dictionaries.senders.create', Auth::user()->organization_id);

        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['order_sender'=>'Нет доступа. Данные не были сохранены.']);

        $data = $request->validated();

        return $service->create($data, true);
    }

    public function update($id, OrderSenderRequest $request, OrderSenderService $service, UsersService $usersService)
    {
        $user_organization_id = Auth::user()->organization_id;
        $order_sender = OrderSender::find($id);

        $check = $usersService->can('menu.main.dictionaries.senders.edit', $user_organization_id);

        if(!$check)
            return $this->errorResponse('Нет доступа', 403, ['order_sender'=>'Нет доступа. Данные не были изменены.']);

        if($order_sender->organization_id != $user_organization_id)
        {
            $check = $usersService->can('menu.main.dictionaries.senders.child_company_senders_edit', $user_organization_id);
            if (!$check)
                return $this->errorResponse('Нет доступа', 403, ['order_sender' => 'Нет доступа. Данные не были изменены.']);
        }

        $data = $request->validated();

        return $service->update($id, $data, true);

    }

    public function delete($id, OrderSenderService $service)
    {
        return $service->delete($id, true);
    }

    public function exToExcel(SearchRequest $request, OrderSenderService $service)
    {
        $request = $service->dxAddPermissions($request, $this->auth->user()['organization_id']);
        return  response()->file($service->exToExcel($request));
    }
}