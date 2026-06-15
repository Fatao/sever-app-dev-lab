<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChangeLog;
use App\Models\LogRequest;
use App\Models\User;
use App\Services\Interfaces\ReportDataCollectorInterface;
use Illuminate\Support\Carbon;

class ReportDataCollector implements ReportDataCollectorInterface
{
    /**
     * Get ranking of most-called controller methods since the given time.
     *
     * @return array<int, array{name: string, count: int, last_operation: string|null}>
     */
    public function getMethodRanking(Carbon $since): array
    {
        return LogRequest::query()
            ->where('called_at', '>=', $since)
            ->whereNotNull('controller_path')
            ->select('controller_path', 'controller_method')
            ->selectRaw('count(*) as total')
            ->selectRaw('max(called_at) as last_call')
            ->groupBy('controller_path', 'controller_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'name'           => $row->controller_path . '@' . $row->controller_method,
                'count'          => (int) $row->total,
                'last_operation' => $row->last_call,
            ])
            ->all();
    }

    /**
     * Get ranking of most-edited entity types since the given time.
     *
     * @return array<int, array{name: string, count: int, last_operation: string|null}>
     */
    public function getEntityRanking(Carbon $since): array
    {
        return ChangeLog::query()
            ->where('created_at', '>=', $since)
            ->select('entity_type')
            ->selectRaw('count(*) as total')
            ->selectRaw('max(created_at) as last_change')
            ->groupBy('entity_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'name'           => $row->entity_type,
                'count'          => (int) $row->total,
                'last_operation' => $row->last_change,
            ])
            ->all();
    }

    /**
     * Get user activity ranking since the given time.
     *
     * Combines: request count, change count, and login count per user.
     *
     * @return array<int, array{name: string, count: int, last_operation: string|null}>
     */
    public function getUserRanking(Carbon $since): array
    {
        $requestCounts = LogRequest::query()
            ->where('called_at', '>=', $since)
            ->whereNotNull('user_id')
            ->select('user_id')
            ->selectRaw('count(*) as total')
            ->selectRaw('max(called_at) as last_call')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $changeCounts = ChangeLog::query()
            ->where('created_at', '>=', $since)
            ->select('created_by')
            ->selectRaw('count(*) as total')
            ->selectRaw('max(created_at) as last_change')
            ->groupBy('created_by')
            ->get()
            ->keyBy('created_by');

        $loginCounts = LogRequest::query()
            ->where('called_at', '>=', $since)
            ->where('method', 'POST')
            ->where('full_url', 'like', '%/api/auth/login')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->selectRaw('count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $userIds = $requestCounts->keys()
            ->merge($changeCounts->keys())
            ->unique()
            ->filter();

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return $userIds->map(function ($userId) use ($requestCounts, $changeCounts, $loginCounts, $users) {
            $user = $users->get($userId);

            $requests = $requestCounts->get($userId);
            $changes  = $changeCounts->get($userId);
            $logins   = (int) ($loginCounts->get($userId) ?? 0);

            $totalCount = ($requests->total ?? 0) + ($changes->total ?? 0) + $logins;

            $lastOperation = collect([
                $requests?->last_call,
                $changes?->last_change,
            ])->filter()->map(fn($d) => Carbon::parse($d))->sort()->last();

            return [
                'name'           => $user?->username ?? "User #{$userId}",
                'count'          => $totalCount,
                'last_operation' => $lastOperation?->toDateTimeString(),
            ];
        })
        ->sortByDesc('count')
        ->values()
        ->all();
    }
}
