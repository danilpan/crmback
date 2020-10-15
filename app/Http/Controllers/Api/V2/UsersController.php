<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\ProfileUpdateRequest;
use App\Http\Requests\Api\V2\UserPasswordUpdateRequest;
use App\Repositories\UsersRepository;
use App\Http\Requests\Api\V2\UserUpdateRequest;
use App\Http\Requests\Api\V2\UserAuthRequest;
use App\Http\Requests\Api\V2\UserSetShowIsWorkRequest;
use App\Services\UsersService;
use App\Services\OrganizationsService;
use Auth;
use Illuminate\Contracts\Auth\Guard;
use Throwable;
use App\Http\Requests\Api\V2\SearchRequest;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class UsersController extends Controller
{
    
    public function dxUsers(SearchRequest $request, UsersService $service){
        $request = $service->dxAddPermissionsWithoutGeo($request, $this->auth->user()['organization_id']);
        return  $service->dxSearch($request);
    }
    
    public function auth(UserAuthRequest $request, Guard $guard, UsersService $usersService, OrganizationsService $organizationsService)
    {

       /* if(request()->headers->get('origin') != "http://localhost:8080"){
            $verifyReCaptcha = $this->verifyReCaptcha($request->get('recaptcha'), request()->ip());
            if($verifyReCaptcha){
                return response()->json(['errors' => [ 'ReCaptcha' => $verifyReCaptcha], 'message' => "Ошибка ReCaptcha верификации", 422]);
            }
        }
        */
        try {
            $request->validated();
            $user_data = [
                "login" => $request->get('login'),
                "password" => $request->get('password'),
            ];

            if ($request->validated() && ($token = $guard->attempt($user_data))) {
                $user = $guard->user();

                $data['status'] = "online";
                $data['last_online'] = date('Y-m-d H:i:s');
                $data['company_id'] =  $organizationsService->getMyCompany($user['organization_id'])['id'];
                $data['pseudo_session'] = substr($token, 0, 255);

                $user = $usersService->update($user['id'], $data, true);
         

                \LogActivity::addToLog('successLogin');
               
                return response()->json(['token' => $token]);
            }
            else {

                \LogActivity::addToLog('notSuccessLogin',['login'=>$request->get('login', 0)]);

                return response()->json(['errors' => [], 'message' => 'wrong credentials'], 422);
            }
        }
        catch(Throwable $e) {
            return response()->json(['errors' => [], 'message' => $e->getMessage()], 422);
        }

    }

    private function verifyReCaptcha($recaptcha, $ip){
        $endpoint = "https://www.google.com/recaptcha/api/siteverify";
        $client = new Client();
        try{
            $response_json = $client->request('POST', $endpoint, ['form_params' => [
                'secret' => '6Le85KQUAAAAAL5SCrlbk2OKoLD6-LGrSkUNKuUu', 
                'response' => $recaptcha, 
                'remoteip' => $ip, 
            ]]);    

            $response = json_decode($response_json->getBody()->getContents(), true);

            if($response['success']){
                return false;
            }else{
                return $response['error-codes'];
            }

        }catch(RequestException $e){
            return $e->getMessage();
        }
    }

    public function getMe(Guard $guard, UsersService $service, OrganizationsService $oService)
    {
        $user = $guard->user();

        $user->permission   = $service->getPermissions($user['organization_id']);

        if(count($user->permission) == 0)
            return response()->json(['errors' => [], 'message' => 'Нет доступов'], 403);
            

        $user->my_company   = $oService->getMyCompany($user['organization_id']);

        return $user;
    }

    public function getByOrganization($id, SearchRequest $request, UsersService $usersService)
    {
        // $filters        = [
        //     'organization.id' => [
        //         'terms' => (int)$id
        //     ]
        // ];
        
        $is_work    = $request->get('is_work');
        $page       = $request->get('page');
        $perPage    = $request->get('per_page');

        // $users  = $usersRepository->search(
        //     $page,
        //     $perPage,
        //     'id',
        //     null,
        //     $filters
        // );
        $user = $this->auth->user();
        $users = $usersService->searchUsers(null, $id, $user, $page, $perPage, $is_work);
// dd($users);
        return $users;
    }

    public function searchUsers(SearchRequest $request, UsersService $usersService)
    {
        $page       = $request->get('page');
        $perPage    = $request->get('per_page');
        $q          = $request->get('query');

        // $users  = $usersRepository->search(
        //     $page,
        //     $perPage,
        //     'id',
        //     null,
        //     null,
        //     $q
        // );

        $user = $this->auth->user();
        $users = $usersService->searchUsers($q , null, $user, $page, $perPage);
        return $users;
    }

    public function getById($id, UsersRepository $usersRepository)
    {
        $user = $usersRepository->find($id);

        if ($user->history()) {
            $user->history_с = $user->history()->get()->map(function ($item) {
                $history_body = json_decode($item['body']);
                if(isset($history_body->main->password))$history_body->main->password = '*****';
                $item['body'] = $history_body;
                return $item;
            });
        }

        return $user;
    }

    public function update($id, UserUpdateRequest $request, UsersService $usersService)
    {
        if($id==0){
            $user = $usersService->create($request->validated(), true);
        }else{
            $user = $usersService->update($id, $request->validated(), true);
        }

        $result['data'] = $user;
        return $result;
    }

    // обновление пароля пользователя
    public function updatePassword($id, UserPasswordUpdateRequest $request)
    {
        if($id == Auth::user()->id) {
            $user = $this->userService->updatePassword($id, $request->validated());
            $result['data'] = $user;
            return $result;
        }else{
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    // страница профайла
    public function getProfile(){
        return $this->userService->getProfile();
    }

    // обновление личных данных пользователя
    public function updateProfile(ProfileUpdateRequest $request, UsersService $usersService)
    {
        $user = $usersService->updateProfile($request->validated(), true);

        return $user;
    }

    public function setShowIsWork($id, UserSetShowIsWorkRequest $request, UsersService $usersService)
    {
        $user = $usersService->setShowIsWork($id, $request->validated(), true);
        $result['data'] = $user;
        return $result;
    }

    public function logoutByRoleChange($role_id, UsersService $usersService)
    {
        $users = $usersService->logoutByRoleChange($role_id);
        $result['data'] = $users;
        return $result;
    }


}
