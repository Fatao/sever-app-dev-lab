<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\RoleDTO;
use App\DTO\RoleCollectionDTO;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Get a list of all roles.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('get-list-role')) {
            return response()->json(['error' => 'Access denied. Required permission: get-list-role'], 403);
        }

        $roles = Role::all();

        $dto = new RoleCollectionDTO(
            roles: $roles->map(fn($role) => RoleDTO::fromModel($role))->toArray(),
            total: $roles->count(),
        );

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Get a specific role by ID.
     */
    public function show(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasPermission('read-role')) {
            return response()->json(['error' => 'Access denied. Required permission: read-role'], 403);
        }

        return response()->json(RoleDTO::fromModel($role)->toArray(), 200);
    }

    /**
     * Create a new role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create([
            'name'        => $request->validated()['name'],
            'slug'        => $request->validated()['slug'],
            'description' => $request->validated()['description'] ?? null,
            'created_by'  => $request->user()->id,
        ]);

        return response()->json(RoleDTO::fromModel($role)->toArray(), 201);
    }

    /**
     * Update an existing role.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update([
            'name'        => $request->validated()['name'],
            'slug'        => $request->validated()['slug'],
            'description' => $request->validated()['description'] ?? null,
        ]);

        return response()->json(RoleDTO::fromModel($role->fresh())->toArray(), 200);
    }

    /**
     * Hard delete a role permanently.
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasPermission('delete-role')) {
            return response()->json(['error' => 'Access denied. Required permission: delete-role'], 403);
        }

        $role->forceDelete();

        return response()->json(['message' => 'Role permanently deleted'], 200);
    }

    /**
     * Soft delete a role.
     */
    public function softDelete(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasPermission('delete-role')) {
            return response()->json(['error' => 'Access denied. Required permission: delete-role'], 403);
        }

        $role->update(['deleted_by' => $request->user()->id]);
        $role->delete();

        return response()->json(['message' => 'Role soft deleted'], 200);
    }

    /**
     * Restore a soft deleted role.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('restore-role')) {
            return response()->json(['error' => 'Access denied. Required permission: restore-role'], 403);
        }

        $role = Role::withTrashed()->findOrFail($id);
        $role->update(['deleted_by' => null]);