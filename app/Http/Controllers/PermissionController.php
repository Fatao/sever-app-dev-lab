<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\PermissionDTO;
use App\DTO\PermissionCollectionDTO;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Get a list of all permissions.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('get-list-permission')) {
            return response()->json(['error' => 'Access denied. Required permission: get-list-permission'], 403);
        }

        $permissions = Permission::all();

        $dto = new PermissionCollectionDTO(
            permissions: $permissions->map(fn($p) => PermissionDTO::fromModel($p))->toArray(),
            total: $permissions->count(),
        );

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Get a specific permission by ID.
     */
    public function show(Request $request, Permission $permission): JsonResponse
    {
        if (!$request->user()->hasPermission('read-permission')) {
            return response()->json(['error' => 'Access denied. Required permission: read-permission'], 403);
        }

        return response()->json(PermissionDTO::fromModel($permission)->toArray(), 200);
    }

    /**
     * Create a new permission.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create([
            'name'        => $request->validated()['name'],
            'slug'        => $request->validated()['slug'],
            'description' => $request->validated()['description'] ?? null,
            'created_by'  => $request->user()->id,
        ]);

        return response()->json(PermissionDTO::fromModel($permission)->toArray(), 201);
    }

    /**
     * Update an existing permission.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update([
            'name'        => $request->validated()['name'],
            'slug'        => $request->validated()['slug'],
            'description' => $request->validated()['description'] ?? null,
        ]);

        return response()->json(PermissionDTO::fromModel($permission->fresh())->toArray(), 200);
    }

    /**
     * Hard delete a permission permanently.
     */
    public function destroy(Request $request, Permission $permission): JsonResponse
    {
        if (!$request->user()->hasPermission('delete-permission')) {
            return response()->json(['error' => 'Access denied. Required permission: delete-permission'], 403);
        }

        $permission->forceDelete();

        return response()->json(['message' => 'Permission permanently deleted'], 200);
    }

    /**
     * Soft delete a permission.
     */
    public function softDelete(Request $request, Permission $permission): JsonResponse
    {
        if (!$request->user()->hasPermission('delete-permission')) {
            return response()->json(['error' => 'Access denied. Required permission: delete-permission'], 403);
        }

        $permission->update(['deleted_by' => $request->user()->id]);
        $permission->delete();

        return response()->json(['message' => 'Permission soft deleted'], 200);
    }

    /**
     * Restore a soft deleted permission.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('restore-permission')) {
            return response()->json(['error' => 'Access denied. Required permission: restore-permission'], 403);
        }

        $permission = Permission::withTrashed()->findOrFail($id);
        $permission->update(['deleted_by' => null]);
        $permission->restore();

        return response()->json(PermissionDTO::fromModel($permission)->toArray(), 200);
    }
}