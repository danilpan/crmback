<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Organization;

class Policy
{
    protected function check(User $user, Organization $organization, $permimssion)
    {
        if(!$this->checkOrganizations($user, $organization)) {
            return false;
        }

        return $this->checkPermission($user, $permimssion);
    }

    protected function checkOrganizations($user, $checkedOrg)
    {
        if(!$user->organization) {
            return false;
        }

        if($user->organization->id == $checkedOrg->id) {
            return true;
        }

        if($checkedOrg->isDescendantOf($user->organization)) {
            return true;
        }

        return false;
    }

    protected function checkPermission($user, $permission)
    {
        if(!$user->organization || !$user->organization->permission) {
            return false;
        }

        $all    = $user->organization->permission->toArray();

        return (bool)array_get($all, $permission);
    }
}
