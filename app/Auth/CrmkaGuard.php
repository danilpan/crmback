<?php
namespace App\Auth;


use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

use Illuminate\Auth\GuardHelpers;
//use Illuminate\Auth\EloquentUserProvider;
//use App\Repositories\UsersRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Tymon\JWTAuth\JWTAuth;
//use Tymon\JWTAuth\Facades\JWTAuth;
//use App\Repositories\UsersRepository

class CrmkaGuard implements Guard
{
    use GuardHelpers;

    protected $userProvider;

    protected $JWTAuth;

    public function __construct(UserProvider $userProvider, JWTAuth $JWTAuth)
    {
        $this->provider     = $userProvider;
        $this->JWTAuth      = $JWTAuth;
    }

    public function user()
    {
        return $this->user;
    }

    public function onceUsingId($id)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return $user;
        }

        return false;
    }


    public function validate(array $credentials = [])
    {

    }


    public function attempt(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if($user && $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);

            $token  = $this->JWTAuth->fromUser($user);

            return $token;
        }


        return null;
    }


//    public function loginUsingId($id)
//    {
//        if (! is_null($user = $this->provider->retrieveById($id))) {
//            return $user;
//        }
//
//        return false;
//    }
//
//    public function getUser()
//    {
//        return $this->user;
//    }
//
//    public function setUser(AuthenticatableContract $user)
//    {
//        $this->user = $user;
//
//        return $this;
//    }
}