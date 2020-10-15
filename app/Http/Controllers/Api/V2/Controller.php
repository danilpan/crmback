<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Database\Eloquent\Collection;
use App\Collections\SearchResultCollection;
use App\Collections\SuggestResultCollection;
use Illuminate\Validation\ValidationException;
use App\Models\ModelIterface as Model;
use Illuminate\Auth\Access\AuthorizationException;

use App\Http\Requests\Api\V2\SuggestRequest;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Services\Service;
use App\Services\UsersService;
use Illuminate\Contracts\Auth\Guard;

class Controller extends BaseController
{
    protected $auth;
    protected $userService;

    public function __construct(Guard $auth, UsersService $userService)
    {
        $this->auth = $auth;
        $this->userService = $userService;
    }

    public function can($permission){
        if (!$this->userService->can($permission, $this->auth->user()['organization_id'])) 
           return $this->errorResponse('not access to - '.$permission, 422);
        return true;
    }

    public function cani($permission){
        if ($this->userService->can($permission, $this->auth->user()['organization_id'])) 
           return true;
        return false;
    }

    public function search(SearchRequest $request, Service $service)
    {
        $query          = $request->get('q');
        $page           = $request->get('page', 1);
        $perPage        = $request->get('per_page', 20);
        $sortKey        = $request->get('sort_key', 'id');
        $sortDirection  = $request->get('sort_direction', 'asc');
        $filters        = $request->get('filters');
        $user           = $this->auth->user();

        $orders         = $service->search($user, $page, $perPage, $sortKey, $sortDirection, $filters, $query);

        return $orders;
    }


    protected function suggest(SuggestRequest $request, Service $service)
    {
        $data       = $request->validated();
        $q          = array_get($data, 'q');
        $filters    = (array)array_get($data, 'filters');
        $user       = $this->auth->user();

        if(empty($q)) {
            return [];
        }

        $models = $service->suggest($user, $q, 200, $filters);

        return $models;
    }


    public function callAction($method, $parameters)
    {
        try {
            $result = parent::callAction($method, $parameters);
        }
        catch(ValidationException $e) {
            $errors     = $e->validator->getMessageBag()->toArray();
            $message    = $e->getMessage();


            return $this->errorResponse($message, 422, $errors);
        }
        catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        //dd($result);
        if($result instanceof SearchResultCollection) {
            if(count($result)) {
                $resource   = $this->getResourceName($result->first());

                return response()->json([
                    'total' => $result->getTotal(),
                    'totalCount' => $result->getTotal(),
                    'data'  => $resource::collection($result)
                ]);
            }
            else {
                return response()->json([
                    'total' => 0,
                    'data'  => []
                ]);
            }
        }

        if($result instanceof Collection) {
            if(count($result)) {
                $resource   = $this->getResourceName($result->first());

                return $resource::collection($result);
            }
            else {
                return response()->json(['data' => []]);
            }
        }



        if($result instanceof Model) {
            $resource   = $this->getResourceName($result);

            return $resource::make($result);
        }


        return $result;
    }

    protected function getResourceName($model)
    {
        $parts      = explode('\\', get_class($model));
        $resource   = 'App\Http\Resources\V2\\' .  end($parts) . 'Resource';

        return $resource;
    }

    protected function errorResponse($message, $code = 500, $errors = [])
    {
        $data   = [
            'message'   => $message
        ];

        if(count($errors)) {
            $data['errors'] = $errors;
        }

        return response()->json($data, $code);
    }

    /**
     * Позволяет вызвать errorResponse, передав аргументы одним массивом
     * @method error
     * @param  Array $args Массив с аргументами для errorResponse
     * @return Response
     */
    protected function error($args)
    {
        return call_user_func_array(array($this, 'errorResponse'), $args);
    }
}
