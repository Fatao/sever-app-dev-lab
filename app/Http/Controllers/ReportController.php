<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerateReportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Dispatch the analytics report generation job to the queue.
     */
    public function generate(Request $request): JsonResponse
    {
        if (!$this->hasPermission($request, 'generate-report')) {
            return response()->json(['error' => 'Access denied. Required permission: generate-report'], 403);
        }

        GenerateReportJob::dispatch();

        return response()->json([
            'message' => 'Report generation has been queued.',
        ], 200);
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
