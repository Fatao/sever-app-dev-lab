<?php

namespace App\Services;

use App\Models\Token;
use App\Models\User;
use App\DTO\AuthSuccessDTO;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TokenService implements Interfaces\TokenServiceInterface
{
    public function generateTokens(User $user): AuthSuccessDTO
    {
        //  Active Token Limit
        $activeTokens = Token::where('user_id', $user->id)
            ->where('type', 'access')
            ->where('revoked', false)
            ->orderBy('created_at')
            ->get();

        if ($activeTokens->count() >= config('tokens.max_active')) {
            $oldest = $activeTokens->first(); // revoke oldest
            $oldest->update(['revoked' => true]);
        }

        //  payload for access token
        $payload = [
            'uid' => $user->id,
            'exp' => now()->addMinutes(config('tokens.access_ttl'))->timestamp,
            'type' => 'access'
        ];

        // Encode
        $base = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $base, config('tokens.secret'));
        $accessToken = $base . '.' . $signature;

        //  Store hashed token only
        Token::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $accessToken),
            'type' => 'access',
            'expires_at' => now()->addMinutes(config('tokens.access_ttl')),
        ]);

        //  refresh token
        $refreshPayload = [
            'uid' => $user->id,
            'exp' => now()->addMinutes(config('tokens.refresh_ttl'))->timestamp,
            'type' => 'refresh'
        ];

        $base = base64_encode(json_encode($refreshPayload));
        $signature = hash_hmac('sha256', $base, config('tokens.secret'));
        $refreshToken = $base . '.' . $signature;

        Token::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $refreshToken),
            'type' => 'refresh',
            'expires_at' => now()->addMinutes(config('tokens.refresh_ttl')),
        ]);

        // Return both tokens
        return new AuthSuccessDTO($accessToken, $refreshToken);
    }
}