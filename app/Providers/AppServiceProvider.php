<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TokenService;
use App\Services\Interfaces\TokenServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TokenServiceInterface::class, TokenService::class);
    }

    public function boot(): void
    {
        //
    }
}