<?php
namespace App\Auth;

use Closure;
use Tymon\JWTAuth\JWTAuth;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Contracts\Auth\Guard;
use App\Services\OrganizationsService;


class JwtMiddleware
{
    protected $auth;

    protected $guard;

    protected $organizationsService;

    public function __construct(JWTAuth $auth, Guard $guard, OrganizationsService $organizationsService)
    {
        $this->auth                 = $auth;
        $this->guard                = $guard;
        $this->organizationsService = $organizationsService;
    }

    public function handle($request, Closure $next)
    {
        $token = $this->auth->setRequest($request)->getToken();

        if(!$token) {
            return response()->json(['error' => 'token not found'], 401);
        }


        try {
            $user = $this->auth->authenticate($token);
            
            if($user['pseudo_session'] != substr($token, 0, 255))
                return response()->json(['error' => 'server have new token'], 401);

            if($user) {
                $organization   = null;
                if($user->organization_id) {
                    $organization       = $this->organizationsService->find($user->organization_id, $user, true, true);
                }

                $user->setRelation('organization', $organization);
                $this->guard->setUser($user);
            }
            else {
                return response()->json(['error' => 'token invalid'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token expired'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'token invalid'], 401);
        }

        return $next($request);
    }
}
