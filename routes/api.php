<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\ChangeLogController;

// Auth routes
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

// RBAC + Changelog routes
Route::prefix('ref')->middleware('token')->group(function () {

    // User management
    Route::prefix('user')->group(function () {
        Route::get('/', [UserRoleController::class, 'index']);
        Route::get('/{user}/role', [UserRoleController::class, 'getUserRoles']);
        Route::post('/{user}/role', [UserRoleController::class, 'attachRole']);
        Route::delete('/{user}/role/{role}', [UserRoleController::class, 'detachRole']);
        Route::delete('/{user}/role/{role}/soft', [UserRoleController::class, 'softDetachRole']);
        Route::post('/{user}/role/{role}/restore', [UserRoleController::class, 'restoreRole']);
        Route::get('/{user}/story', [ChangeLogController::class, 'userStory']);
    });

    // Role management
    Route::prefix('policy/role')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/{role}/story', [ChangeLogController::class, 'roleStory']);
        Route::get('/{role}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{role}', [RoleController::class, 'update']);
        Route::patch('/{role}', [RoleController::class, 'update']);
        Route::delete('/{role}', [RoleController::class, 'destroy']);
        Route::delete('/{role}/soft', [RoleController::class, 'softDelete']);
        Route::post('/{id}/restore', [RoleController::class, 'restore']);
    });

    // Permission management
    Route::prefix('policy/permission')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('/{permission}/story', [ChangeLogController::class, 'permissionStory']);
        Route::get('/{permission}', [PermissionController::class, 'show']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::put('/{permission}', [PermissionController::class, 'update']);
        Route::patch('/{permission}', [PermissionController::class, 'update']);
        Route::delete('/{permission}', [PermissionController::class, 'destroy']);
        Route::delete('/{permission}/soft', [PermissionController::class, 'softDelete']);
        Route::post('/{id}/restore', [PermissionController::class, 'restore']);
    });

    // Changelog undo/restore
    Route::post('/changelog/{log}/restore', [ChangeLogController::class, 'restore']);
});