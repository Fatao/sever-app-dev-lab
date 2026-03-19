<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;

class TokenMiddleware
{
    /**
     * Handle an incoming request and validate the bearer token.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        // Hash the token to look it up in DB
        $tokenHash = hash('sha256', $token);

        $tokenModel = Token::where('token_hash', $tokenHash)
            ->where('type', 'access')
            ->where('revoked', false)
            ->first();

        if (!$tokenModel) {
            return response()->json(['error' => 'Token invalid or revoked'], 401);
        }

        // Check expiration
        if (Carbon::now()->greaterThan($tokenModel->expires_at)) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        // Update last used
        $tokenModel->update(['last_used_at' => Carbon::now()]);

        $user = User::find($tokenModel->user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}