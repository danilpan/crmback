<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Provider;
use App\Http\Requests\Api\V2\ProviderRequest;
use App\Repositories\ProviderRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\ProviderService;
use App\Services\UsersService;
use Auth;
use Request;

class ProviderController extends Controller
{
    protected $usersService;
    
    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }
    
    public function can($type)
    {
        return $this->usersService->can("menu.main.providers.$type",Auth::user()->organization_id);
    }
    
    /**
     * Возвращает список всех Provider
     */
    public function index(SearchRequest $request, ProviderService $service){
        $ref_arr = explode('/', Request::server('HTTP_REFERER'));
        $force_access = end($ref_arr) === '' && count($ref_arr) == 4;
        if (!$this->can("view") && !$force_access) {
            return $this->errorResponse('Нет доступа', 403, ['providers_view' => 'Вы не можете просматривать провайдеров связи']);
        }
        return $service->index($request);
    }

    /**
     * Создает новую запись в таблице Provider
     */
    public function store(ProviderRequest $request, ProviderService $service)
    {
        if (!$this->can("create")) {
            return $this->errorResponse('Нет доступа', 403, ['providers_create' => 'Вы не можете создавать провайдеров связи']);
        }
        $data = $request->validated();
        if($request->logo){
            $path = public_path('img/providers/');
            if (!file_exists($path) || !is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    return $this->errorResponse("Нет каталога", 500, ["provider_logo" => "Не удалось создать каталог $path"]);
                }
            }
            $filename = preg_replace('/[^a-zа-яё\d]/ui', '', $data["name"]) . "_logo";
            $ext = $service->getImageExtension($request->logo);
            if ($ext) {
                imagejpeg($service->resizeImage($request->logo->getPathName(), $ext, 200, 200), $path.$filename.".jpg");
                unlink($request->logo->getPathName());
                $data["img"] = '/img/providers/'.$filename.".jpg";
            } else {
                $data["img"] = "/img/noimage.gif";
                unlink($request->logo->getPathName());
                unset($data["logo"]);
                $provider = $service->create($data, true);
                return $this->errorResponse("Предупреждение", 422, ["provider_logo" => "Неправильный тип изображения"]);
            }
        } else {
            $data["img"] = "/img/noimage.gif";
        }
        unset($data["logo"]);
        $provider = $service->create($data, true);
        return $provider;
    }

    /**
     * Возвращает Provider по ID
     */
    public function show($id, ProviderRepository $repository)
    {
        if (!$this->can("view")) {
            return $this->errorResponse('Нет доступа', 403, ['providers_view' => 'Вы не можете просматривать провайдеров связи']);
        }
        $provider = $repository->find($id);
        if (!$provider) {
            return $this->errorResponse('Не найдено', 404, ['provider'=>'Provider с ID '.$id.' не существует']);
        }
        return $provider;
    }

    /**
     * Редактирует Provider по ID
     */
    public function update($id, ProviderRequest $request, ProviderService $service, ProviderRepository $repository)
    {
        if (!$this->can("edit")) {
            return $this->errorResponse('Нет доступа', 403, ['providers_edit' => 'Вы не можете редактировать провайдеров связи']);
        }
        $provider = $repository->find($id);
        if (!$provider) {
            return $this->errorResponse('Не найдено', 404, ['provider'=>'Provider с ID '.$id.' не существует']);
        }
        $data = $request->validated();
        if($request->logo){
            $path = public_path('img/providers/');
            if (!file_exists($path) || !is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    return $this->errorResponse("Нет каталога", 500, ["provider_logo" => "Не удалось создать каталог $path"]);
                }
            }
            $filename = $data["img"];
            $ext = $service->getImageExtension($request->logo);
            if ($ext) {
                imagejpeg($service->resizeImage($request->logo->getPathName(), $ext, 200, 200), "$path../..$filename");
                unlink($request->logo->getPathName());
                $data["img"] = $filename;
            } else {
                if ($provider->img) {
                    $data["img"] = $provider->img;
                } else {
                    $data["img"] = "/img/noimage.gif";
                }
                unlink($request->logo->getPathName());
                unset($data["logo"]);
                $provider = $service->create($data, true);
                return $this->errorResponse("Предупреждение", 422, ["provider_logo" => "Неправильный тип изображения"]);
            }
        } else {
            if ($provider->img) {
                $data["img"] = $provider->img;
            } else {
                $data["img"] = "/img/noimage.gif";
            }            
        }
        unset($data["logo"]);
        $provider = $service->update($id, $data, true);
        return $provider;
    }

    /**
     * Удаляет Provider по ID
     */
    public function destroy($id, ProviderRepository $repository)
    {
        if (!$this->can("delete")) {
            return $this->errorResponse('Нет доступа', 403, ['providers_delete' => 'Вы не можете удалять провайдеров связи']);
        }
        $provider = $repository->find($id);
        if (!$provider) {
            return $this->errorResponse('Не найдено', 404, ['provider'=>'Provider с ID '.$id.' не существует']);
        }
        $repository->deleteFromIndex($provider);
        return $repository->delete($id);
    }
}