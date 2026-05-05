<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\TemporaryTokenServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TemporaryTokenService implements TemporaryTokenServiceInterface
{
    private int $ttl = 300; // 5 minutes

    /**
     * Generate a temporary token for 2FA flow.
     */
    public function generate(User $user, string $clientSignature): string
    {
        $payload = [
            'uid' => $user->id,
            'sig' => $clientSignature,
            'exp' => Carbon::now()->addSeconds($this->ttl)->timestamp,
            'rnd' => bin2hex(random_bytes(8)),
        ];

        $token = base64_encode(json_encode($payload));

        Cache::put(
            "temp_token_{$token}",
            $payload,
            Carbon::now()->addSeconds($this->ttl)
        );

        return $token;
    }

    /**
     * Validate a temporary token and return user ID if valid.
     */
    public function validate(string $token, string $clientSignature): ?int
    {
        $payload = Cache::get("temp_token_{$token}");

        if (!$payload) {
            return null;
        }

        if ($payload['exp'] < Carbon::now()->timestamp) {
            $this->invalidate($token);
            return null;
        }

        if ($payload['sig'] !== $clientSignature) {
            return null;
        }

        return (int) $payload['uid'];
    }

    /**
     * Invalidate a temporary token by removing it from cache.
     */
    public function invalidate(string $token): void
    {
        Cache::forget("temp_token_{$token}");
    }
}