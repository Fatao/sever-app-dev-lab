<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        // Check header
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        // Validate token format
        $parts = explode('.', $token);

        if (count($parts) !== 2) {
            return response()->json(['error' => 'Invalid token format'], 401);
        }

        [$base64, $signature] = $parts;

        // Verify signature
        $expected = hash_hmac('sha256', $base64, config('tokens.secret'));

        if (!hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid token signature'], 401);
        }

        // Decode payload
        $payload = json_decode(base64_decode($base64), true);

        if (!$payload || !isset($payload['uid'], $payload['exp'])) {
            return response()->json(['error' => 'Invalid token payload'], 401);
        }

        // Expiration
        if ($payload['exp'] < now()->timestamp) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        // Check DB token
        $tokenModel = Token::where('revoked', false)
            ->get()
            ->first(fn ($t) => Hash::check($token, $t->token));

        if (!$tokenModel) {
            return response()->json(['error' => 'Token revoked or invalid'], 401);
        }

        // Attach user
        $user = User::find($payload['uid']);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}