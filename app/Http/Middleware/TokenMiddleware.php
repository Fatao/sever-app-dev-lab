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

        // 1. Check header exists
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        // 2. Validate token format BEFORE explode
        if (substr_count($token, '.') !== 1) {
            return response()->json(['error' => 'Malformed token'], 401);
        }

        [$base64, $signature] = explode('.', $token);

        // 3. Verify signature
        $expected = hash_hmac('sha256', $base64, config('tokens.secret'));

        if (!hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid token signature'], 401);
        }

        // 4. Decode payload safely
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            return response()->json(['error' => 'Invalid base64'], 401);
        }

        $payload = json_decode($decoded, true);

        if (!$payload || !isset($payload['uid'], $payload['exp'])) {
            return response()->json(['error' => 'Invalid payload'], 401);
        }

        // 5. Expiration check
        if ($payload['exp'] < now()->timestamp) {
            return response()->json(['error' => 'Token expired'], 401);
        }

        // 6. DB check (optimized)
        $tokenModel = Token::where('revoked', false)
            ->get()
            ->first(fn ($t) => Hash::check($token, $t->token));

        if (!$tokenModel) {
            return response()->json(['error' => 'Token invalid or revoked'], 401);
        }

        // 7. Attach user
        $user = User::find($payload['uid']);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}