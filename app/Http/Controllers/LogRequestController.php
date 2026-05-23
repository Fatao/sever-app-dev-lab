<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\LogRequestCollectionDTO;
use App\DTO\LogRequestDTO;
use App\DTO\LogRequestListItemDTO;
use App\Http\Requests\LogRequestIndexRequest;
use App\Models\LogRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogRequestController extends Controller
{
    /**
     * Get paginated list of logs with optional filtering and sorting.
     */
    public function index(LogRequestIndexRequest $request): JsonResponse
    {
        if (!$this->hasPermission($request, 'get-list-log')) {
            return response()->json(['error' => 'Access denied. Required permission: get-list-log'], 403);
        }

        $data  = $request->validated();
        $page  = (int) ($data['page']  ?? 1);
        $count = (int) ($data['count'] ?? 10);

        $query = LogRequest::query();

        // Apply filters
        foreach ($data['filter'] ?? [] as $filter) {
            if ($filter['key'] === 'user_agent') {
                $query->where($filter['key'], 'like', '%' . $filter['value'] . '%');
            } else {
                $query->where($filter['key'], $filter['value']);
            }
        }

        // Apply sorting
        foreach ($data['sortBy'] ?? [] as $sort) {
            $query->orderBy($sort['key'], $sort['order']);
        }

        $paginated = $query->paginate($count, ['*'], 'page', $page);

        $items = collect($paginated->items())
            ->map(fn(LogRequest $log) => LogRequestListItemDTO::fromModel($log))
            ->all();

        $dto = new LogRequestCollectionDTO(
            items:       $items,
            total:       $paginated->total(),
            currentPage: $paginated->currentPage(),
            lastPage:    $paginated->lastPage(),
            perPage:     $paginated->perPage(),
        );

        return response()->json($dto->toArray(), 200);
    }

    /**
     * Get full details of a specific log entry.
     */
    public function show(Request $request, LogRequest $logRequest): JsonResponse
    {
        if (!$this->hasPermission($request, 'read-log')) {
            return response()->json(['error' => 'Access denied. Required permission: read-log'], 403);
        }

        return response()->json(LogRequestDTO::fromModel($logRequest)->toArray(), 200);
    }

    /**
     * Permanently delete a specific log entry.
     */
    public function destroy(Request $request, LogRequest $logRequest): JsonResponse
    {
        if (!$this->hasPermission($request, 'delete-log')) {
            return response()->json(['error' => 'Access denied. Required permission: delete-log'], 403);
        }

        $logRequest->delete();

        return response()->json(null, 204);
    }

    /**
     * Check if authenticated user has a specific permission.
     */
    private function hasPermission(Request $request, string $permissionSlug): bool
    {
        $user = $request->user();

        if (!$user) {
            return false;
        }

        return $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions)
            ->contains('slug', $permissionSlug);
    }
}