<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Auth\CrmkaGuard;
use Auth;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;




class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\Organization::class     => \App\Policies\OrganizationPolicy::class,
        \App\Models\Permission::class     => \App\Policies\PermissionPolicy::class
//        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();


        Auth::extend('crmka', function ($app, $name, array $config) {
            return new CrmkaGuard(
                new EloquentUserProvider(
                    $this->app->make(HasherContract::class),
                    User::class
                ),
                $this->app->make(JWTAuth::class)
            );
        });
    }
}
