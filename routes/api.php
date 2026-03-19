<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::middleware('token')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/out', [AuthController::class, 'out']);
        Route::post('/out_all', [AuthController::class, 'outAll']);
        Route::get('/tokens', [AuthController::class, 'tokens']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

});