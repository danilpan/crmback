<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Organization;

class PermissionPolicy extends Policy
{
    public function list(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.permissions.list');
    }

    public function view(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.permissions.view');
    }

    public function create(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.permissions.create');
    }

    public function update(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.permissions.update');
    }

    public function delete(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.permissions.delete');
    }
}
