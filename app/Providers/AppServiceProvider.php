<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\TwoFactorService;
use App\Services\TemporaryTokenService;
use App\Services\DeploymentService;
use App\Services\DeploymentLogger;
use App\Services\Interfaces\TwoFactorServiceInterface;
use App\Services\Interfaces\TemporaryTokenServiceInterface;
use App\Services\Interfaces\DeploymentServiceInterface;
use App\Services\Interfaces\DeploymentLoggerInterface;
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
        $this->app->bind(TwoFactorServiceInterface::class, TwoFactorService::class);
        $this->app->bind(TemporaryTokenServiceInterface::class, TemporaryTokenService::class);
        $this->app->bind(DeploymentLoggerInterface::class, DeploymentLogger::class);
        $this->app->bind(DeploymentServiceInterface::class, DeploymentService::class);
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