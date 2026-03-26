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
     * Get list of all users with their roles.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('get-list-user')) {
            return response()->json(['error' => 'Access denied. Required permission: get-list-user'], 403);
        }

        $users = User::with(['roles'])->get();

        $data = $users->map(fn($user) => new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday?->format('Y-m-d') ?? '',
            roles: $user->roles->map(fn($role) => RoleDTO::fromModel($role))->toArray(),
        ))->map(fn($dto) => $dto->toArray())->toArray();

        return response()->json(['data' => $data, 'total' => count($data)], 200);
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

        $dto = new RoleCollectionDTO(
            roles: $roles->map(fn($role) => RoleDTO::fromModel($role))->toArray(),
            total: $roles->count(),
        );

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Assign a role to a user.
     */
    public function attachRole(AttachUserRoleRequest $request, User $user): JsonResponse
    {
        $roleId = $request->validated()['role_id'];

        // Check if active link already exists
        $existing = UserRole::where('user_id', $user->id)
            ->where('role_id', $roleId)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Role already assigned to user'], 422);
        }

        // Check if soft deleted link exists — restore it
        $softDeleted = UserRole::where('user_id', $user->id)
            ->where('role_id', $roleId)
            ->whereNotNull('deleted_at')
            ->first();

        if ($softDeleted) {
            $softDeleted->update([
                'deleted_at' => null,
                'deleted_by' => null,
                'created_by' => $request->user()->id,
                'created_at' => Carbon::now(),
            ]);

            return response()->json(['message' => 'Role restored and assigned to user'], 200);
        }

        UserRole::create([
            'user_id'    => $user->id,
            'role_id'    => $roleId,
            'created_by' => $request->user()->id,
            'created_at' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Role assigned to user'], 201);
    }

    /**
     * Hard delete a role from a user.
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
     * Soft delete a role from a user.
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
            return response()->json(['error' => 'Role not assigned to user'], 404);
        }

        $userRole->update(['deleted_by' => $request->user()->id]);
        $userRole->delete();

        return response()->json(['message' => 'Role soft removed from user'], 200);
    }

    /**
     * Restore a soft deleted role assignment.
     */
    public function restoreRole(Request $request, User $user, Role $role): JsonResponse
    {
        if (!$request->user()->hasPermission('restore-user')) {
            return response()->json(['error' => 'Access denied. Required permission: restore-user'], 403);
        }

        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$userRole) {
            return response()->json(['error' => 'No soft deleted role found for user'], 404);
        }

        $userRole->update(['deleted_by' => null]);
        $userRole->restore();

        return response()->json(['message' => 'Role assignment restored'], 200);
    }
}