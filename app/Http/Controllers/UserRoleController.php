<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\RoleDTO;
use App\DTO\RoleCollectionDTO;
use App\DTO\UserDTO;
use App\Http\Requests\AttachUserRoleRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserRoleController extends Controller
{
    /**
     * Get list of all users (with their roles).
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('get-list-user')) {
            return response()->json(['error' => 'Access denied. Required permission: get-list-user'], 403);
        }

        $users = User::with('roles')->get();

        $data = $users->map(fn($user) => UserDTO::fromModel($user)->toArray())->toArray();

        return response()->json([
            'data'  => $data,
            'total' => count($data)
        ], 200);
    }

    /**
     * Get roles of a specific user.
     */
    public function getUserRoles(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->hasPermission('read-user')) {
            return response()->json(['error' => 'Access denied. Required permission: read-user'], 403);
        }

        $roles = $user->roles;
        $dtos = $roles->map(fn($role) => RoleDTO::fromModel($role))->toArray();

        $collection = new RoleCollectionDTO($dtos, $roles->count());

        return response()->json($collection->toArray(), 200);
    }

    /**
     * Attach a role to a user.
     */
    public function attachRole(AttachUserRoleRequest $request, User $user): JsonResponse
    {
        $roleId = $request->validated()['role_id'];

        // Check for active assignment
        if (UserRole::where('user_id', $user->id)
                    ->where('role_id', $roleId)
                    ->whereNull('deleted_at')
                    ->exists()) {
            return response()->json(['error' => 'Role already assigned to user'], 422);
        }

        // Restore if soft deleted
        $softDeleted = UserRole::withTrashed()
            ->where('user_id', $user->id)
            ->where('role_id', $roleId)
            ->whereNotNull('deleted_at')
            ->first();

        if ($softDeleted) {
            $softDeleted->restore();
            return response()->json(['message' => 'Role assignment restored'], 200);
        }

        // Create new assignment
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $roleId,
        ]);

        return response()->json(['message' => 'Role assigned successfully'], 201);
    }

    /**
     * Hard detach role from user.
     */
    public function detachRole(Request $request, User $user, Role $role): JsonResponse
    {
        if (!$request->user()->hasPermission('delete-user')) {
            return response()->json(['error' => 'Access denied. Required permission: delete-user'], 403);
        }

        UserRole::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->forceDelete();

        return response()->json(['message' => 'Role permanently removed from user'], 200);
    }

    /**
     * Soft detach role from user.
     */
    public function softDetachRole(Request $request, User $user, Role $role): JsonResponse
    {
        if (!$request->user()->hasPermission('delete-user')) {
            return response()->json(['error' => 'Access denied. Required permission: delete-user'], 403);
        }

        $userRole = UserRole::where('user_id', $user->id)
                            ->where('role_id', $role->id)
                            ->whereNull('deleted_at')
                            ->first();

        if (!$userRole) {
            return response()->json(['error' => 'No active role assignment found'], 404);
        }

        $userRole->delete();   // Soft delete
        return response()->json(['message' => 'Role soft removed from user'], 200);
    }

    /**
     * Restore soft deleted role assignment.
     */
    public function restoreRole(Request $request, User $user, Role $role): JsonResponse
    {
        if (!$request->user()->hasPermission('restore-user')) {
            return response()->json(['error' => 'Access denied. Required permission: restore-user'], 403);
        }

        $userRole = UserRole::withTrashed()
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$userRole) {
            return response()->json(['error' => 'No soft deleted assignment found'], 404);
        }

        $userRole->restore();
        return response()->json(['message' => 'Role assignment restored'], 200);
    }
}