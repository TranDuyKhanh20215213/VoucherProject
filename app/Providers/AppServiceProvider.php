<?php

namespace App\Providers;

//use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Admin;
use App\Policies\UserPolicy;
use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    protected $policies = [
        User::class => UserPolicy::class,
        Admin::class => AdminPolicy::class
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('user-changePassword', [UserPolicy::class, 'changePassword']); 
        Gate::define('user-updateProfile', [UserPolicy::class, 'updateProfile']); 
        Gate::define('user-getProfile', [UserPolicy::class, 'getProfile']);  

        Gate::define('admin-changePassword', [AdminPolicy::class, 'changePassword']);
        Gate::define('admin-updateProfile', [AdminPolicy::class, 'updateProfile']);
        Gate::define('admin-getProfile', [AdminPolicy::class, 'getProfile']);
    }
}
