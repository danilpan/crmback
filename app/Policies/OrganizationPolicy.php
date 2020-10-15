<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Organization;

class OrganizationPolicy extends Policy
{
    public function list(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.organizations.list');
    }

    public function view(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.organizations.view');
    }

    public function create(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.organizations.create');
    }

    public function update(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.organizations.update');
    }

    public function delete(User $user, Organization $organization)
    {
        return $this->check($user, $organization, 'api.organizations.delete');
    }
}
