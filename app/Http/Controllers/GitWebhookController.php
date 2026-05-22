<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GitWebhookRequest;
use App\Services\Interfaces\DeploymentServiceInterface;
use Illuminate\Http\JsonResponse;

class GitWebhookController extends Controller
{
    public function __construct(
        private readonly DeploymentServiceInterface $deploymentService,
    ) {}

    /**
     * Handle an incoming Git webhook request.
     *
     * Validates the secret key, acquires a lock, runs git commands,
     * logs the result and returns a JSON response.
     */
    public function __invoke(GitWebhookRequest $request): JsonResponse
    {
        // Validate secret key with strict comparison
        $secretKey = $request->input('secret_key');

        if ($secretKey !== config('git.webhook_secret')) {
            return response()->json(['error' => 'Invalid secret key'], 403);
        }

        // Check deployment lock
        if ($this->deploymentService->isLocked()) {
            return response()->json(['error' => 'Deployment already in progress'], 409);
        }

        // Acquire lock before starting
        $this->deploymentService->acquireLock();

        try {
            $result = $this->deploymentService->deploy($request->ip());
        } finally {
            // Always release lock even if deployment fails
            $this->deploymentService->releaseLock();
        }

        $statusCode = $result->status === 'success' ? 200 : 500;

        return response()->json($result->toArray(), $statusCode);
    }
}