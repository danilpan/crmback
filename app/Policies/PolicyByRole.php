<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\UsersService;

class PolicyByRole
{
    protected $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    protected function check($permission, $user)
    {
        if(!$user)
            return false;
               
        return $this->usersService->can($user['organization_id'], $permission);
    }
}
