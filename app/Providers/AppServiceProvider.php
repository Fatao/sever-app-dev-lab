<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use App\Services\AuditService;
use App\Services\Interfaces\AuditServiceInterface;
use App\Services\Interfaces\TokenServiceInterface;
use App\Services\TokenService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application service bindings.
     */
    public function register(): void
    {
        $this->app->bind(TokenServiceInterface::class, TokenService::class);
        $this->app->bind(AuditServiceInterface::class, AuditService::class);
    }

    /**
     * Bootstrap application services and register observers.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
    }
}