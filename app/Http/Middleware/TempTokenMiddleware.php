<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Interfaces\TemporaryTokenServiceInterface;
use Closure;
use Illuminate\Http\Request;

class TempTokenMiddleware
{
    public function __construct(
        private readonly TemporaryTokenServiceInterface $tempTokenService,
    ) {}

    /**
     * Validate the temporary 2FA token and attach the user to the request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Temporary token required'], 401);
        }

        $token           = substr($authHeader, 7);
        $clientSignature = $this->buildClientSignature($request);
        $userId          = $this->tempTokenService->validate($token, $clientSignature);

        if (!$userId) {
            return response()->json(['error' => 'Invalid or expired temporary token'], 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('temp_token', $token);
        $request->attributes->set('client_signature', $clientSignature);

        return $next($request);
    }

    /**
     * Build a deterministic client signature from IP + UserAgent + app key.
     */
    public function buildClientSignature(Request $request): string
    {
        return hash('sha256', $request->ip() . $request->userAgent() . config('app.key'));
    }
}