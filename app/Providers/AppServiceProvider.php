<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Status;
use App\Models\LogActivity;
use App\Observers\OrderObserver;
use App\Observers\StatusObserver;
use App\Observers\LogActivityObserver;
use App\Models\User;
use App\Observers\UserObserver;

use Illuminate\Support\ServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Order::observe(OrderObserver::class);
        Status::observe(StatusObserver::class);
        LogActivity::observe(LogActivityObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        if ($this->app->environment() !== 'production') {
//            $this->app->register(IdeHelperServiceProvider::class);
//        }

    }
}
