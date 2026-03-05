<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Token;
use App\Models\User;

class TokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);
        [$base64, $signature] = explode('.', $token);

        // Verify HMAC signature
        $expected = hash_hmac('sha256', $base64, config('tokens.secret'));
        if (!hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Decode payload
        $payload = json_decode(base64_decode($base64), true);
        if (!$payload || !isset($payload['uid'], $payload['exp'])) {
            return response()->json(['error' => 'Invalid token payload'], 401);
        }

        // Check expiration
        if ($payload['exp'] < now()->timestamp) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        // Check database
        $tokenHash = hash('sha256', $token);
        $tokenModel = Token::where('token_hash', $tokenHash)
            ->where('revoked', false)
            ->first();

        if (!$tokenModel) {
            return response()->json(['error' => 'Token revoked or invalid'], 401);
        }

        // Attach user
        $user = User::find($payload['uid']);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        $request->merge(['user' => $user]);

        return $next($request);
    }
}