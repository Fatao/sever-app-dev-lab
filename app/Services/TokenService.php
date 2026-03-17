<?php

namespace App\Services;

use App\DTO\AuthSuccessDTO;
use App\Models\User;
use App\Models\Token;
use App\Services\Interfaces\TokenServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Exception;

class TokenService implements TokenServiceInterface
{
    private int $accessTTL;
    private int $refreshTTL;
    private int $maxActiveTokens;

    public function __construct()
    {
        $this->accessTTL = env('ACCESS_TOKEN_TTL', 60);
        $this->refreshTTL = env('REFRESH_TOKEN_TTL', 10080);
        $this->maxActiveTokens = env('MAX_ACTIVE_TOKENS', 5);
    }

    public function generateTokens(User $user): AuthSuccessDTO
    {
        // Limit active tokens
        $activeTokens = Token::where('user_id', $user->id)
            ->where('type', 'access')
            ->where('revoked', false)
            ->orderBy('created_at')
            ->get();

        if ($activeTokens->count() >= $this->maxActiveTokens) {
            $oldest = $activeTokens->first();
            $oldest->update(['revoked' => true]);
        }

        // ACCESS TOKEN
        $accessToken = $this->createTokenString($user->id, 'access', $this->accessTTL);

        Token::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $accessToken),
            'type' => 'access',
            'expires_at' => Carbon::now()->addMinutes($this->accessTTL),
        ]);

        // REFRESH TOKEN
        $refreshToken = $this->createTokenString($user->id, 'refresh', $this->refreshTTL);

        Token::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $refreshToken),
            'type' => 'refresh',
            'expires_at' => Carbon::now()->addMinutes($this->refreshTTL),
        ]);

        return new AuthSuccessDTO(
            access_token: $accessToken,
            refresh_token: $refreshToken,
            user: $user
        );
    }

    public function refresh(string $refreshToken): AuthSuccessDTO
    {
        $tokenHash = hash('sha256', $refreshToken);

        $tokenModel = Token::where('token_hash', $tokenHash)
            ->where('type', 'refresh')
            ->where('revoked', false)
            ->first();

        if (!$tokenModel) {
            throw new Exception('Invalid refresh token');
        }

        if (Carbon::now()->greaterThan($tokenModel->expires_at)) {
            throw new Exception('Refresh token expired');
        }

        $tokenModel->update(['revoked' => true]);

        return $this->generateTokens($tokenModel->user);
    }

    public function revoke(string $token): void
    {
        $tokenHash = hash('sha256', $token);

        Token::where('token_hash', $tokenHash)
            ->update(['revoked' => true]);
    }

    public function revokeAll(User $user): void
    {
        Token::where('user_id', $user->id)
            ->update(['revoked' => true]);
    }

    public function validateAccessToken(string $token): ?User
    {
        $tokenHash = hash('sha256', $token);

        $tokenModel = Token::where('token_hash', $tokenHash)
            ->where('type', 'access')
            ->where('revoked', false)
            ->first();

        if (!$tokenModel) {
            return null;
        }

        if (Carbon::now()->greaterThan($tokenModel->expires_at)) {
            return null;
        }

        return $tokenModel->user;
    }

    private function createTokenString(int $userId, string $type, int $ttlMinutes): string
    {
        $payload = [
            'uid' => $userId,
            'type' => $type,
            'exp' => Carbon::now()->addMinutes($ttlMinutes)->timestamp,
            'rnd' => Str::random(16)
        ];

        return base64_encode(json_encode($payload));
    }
}