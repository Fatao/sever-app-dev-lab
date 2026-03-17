<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\TokenServiceInterface;
use App\Services\TokenService;

class AppServiceProvider extends ServiceProvider
{
    
     // Register any application services.
     
    public function register(): void
    {
        $this->app->bind(
            TokenServiceInterface::class,
            TokenService::class
        );
    }

    
     // Bootstrap any application services.
     
    public function boot(): void
    {
        //
    }
}