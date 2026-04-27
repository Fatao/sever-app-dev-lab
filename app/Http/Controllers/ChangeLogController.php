<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ChangeLogCollectionDTO;
use App\DTO\ChangeLogDTO;
use App\Models\ChangeLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChangeLogController extends Controller
{
    /**
     * Get change history for a specific user.
     */
    public function userStory(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if (!$authUser || !$this->hasPermission($authUser, 'get-story-user')) {
            return response()->json(['error' => 'Access denied. Required permission: get-story-user'], 403);
        }

        return $this->buildStoryResponse('user', $user->id);
    }

    /**
     * Get change history for a specific role.
     */
    public function roleStory(Request $request, Role $role): JsonResponse
    {
        $authUser = $request->user();

        if (!$authUser || !$this->hasPermission($authUser, 'get-story-role')) {
            return response()->json(['error' => 'Access denied. Required permission: get-story-role'], 403);
        }

        return $this->buildStoryResponse('role', $role->id);
    }

    /**
     * Get change history for a specific permission.
     */
    public function permissionStory(Request $request, Permission $permission): JsonResponse
    {
        $authUser = $request->user();

        if (!$authUser || !$this->hasPermission($authUser, 'get-story-permission')) {
            return response()->json(['error' => 'Access denied. Required permission: get-story-permission'], 403);
        }

        return $this->buildStoryResponse('permission', $permission->id);
    }

    /**
     * Restore an entity to the state recorded in the log's before field.
     */
    public function restore(Request $request, ChangeLog $log): JsonResponse
    {
        $authUser   = $request->user();
        $permission = 'update-' . $log->entity_type;

        if (!$authUser || !$this->hasPermission($authUser, $permission)) {
            return response()->json(['error' => 'Access denied. Required permission: ' . $permission], 403);
        }

        return DB::transaction(function () use ($log): JsonResponse {
            $before = $log->before;

            if (empty($before)) {
                return response()->json(['error' => 'Nothing to restore: before state is empty.'], 422);
            }

            $model = $this->resolveModel($log->entity_type, $log->entity_id);

            if (!$model) {
                return response()->json(['error' => 'Entity not found.'], 404);
            }

            $safeFields = array_diff_key(
                $before,
                array_flip(['id', 'created_at', 'password', 'remember_token'])
            );

            $model->update($safeFields);

            return response()->json([
                'message' => 'Entity restored to previous state.',
                'log_id'  => $log->id,
            ], 200);
        });
    }

    /**
     * Check if a user has a specific permission via their roles.
     */
    private function hasPermission(User $user, string $permissionSlug): bool
    {
        return $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions)
            ->contains('slug', $permissionSlug);
    }

    /**
     * Build a JSON response with the change history collection.
     */
    private function buildStoryResponse(string $entityType, int $entityId): JsonResponse
    {
        $logs = ChangeLog::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->get();

        $items = $logs->map(fn(ChangeLog $log) => ChangeLogDTO::fromModel($log))->all();
        $dto   = new ChangeLogCollectionDTO(items: $items, total: count($items));

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Resolve the Eloquent model by entity type and ID.
     */
    private function resolveModel(string $entityType, int $entityId): ?object
    {
        return match ($entityType) {
            'user'       => User::withTrashed()->find($entityId),
            'role'       => Role::withTrashed()->find($entityId),
            'permission' => Permission::withTrashed()->find($entityId),
            default      => null,
        };
    }
}