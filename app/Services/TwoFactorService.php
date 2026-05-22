<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TwoFactorCode;
use App\Models\User;
use App\Services\Interfaces\TwoFactorServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class TwoFactorService implements TwoFactorServiceInterface
{
    private int $codeTtl    = 300;  // 5 minutes
    private int $maxPerClient = 3;
    private int $maxPerUser   = 5;
    private int $clientDelay  = 30; // seconds
    private int $userDelay    = 50; // seconds

    /**
     * Generate a new 2FA code for the user, invalidating previous ones.
     */
    public function generateCode(User $user, string $clientSignature): string
    {
        $this->checkRateLimits($user->id, $clientSignature);

        // Invalidate previous unused codes for this client
        $this->invalidateCodes($user, $clientSignature);

        $plainCode = (string) random_int(100000, 999999);

        TwoFactorCode::create([
            'user_id'          => $user->id,
            'code'             => Hash::make($plainCode),
            'client_signature' => $clientSignature,
            'expires_at'       => Carbon::now()->addSeconds($this->codeTtl),
            'attempts'         => 0,
        ]);

        // Increment request counters
        $clientKey = "2fa_request_client_{$clientSignature}";
        $userKey   = "2fa_request_user_{$user->id}";
        Cache::increment($clientKey);
        Cache::increment($userKey);
        Cache::put($clientKey, Cache::get($clientKey, 1), Carbon::now()->addHour());
        Cache::put($userKey,   Cache::get($userKey,   1), Carbon::now()->addHour());

        return $plainCode;
    }

    /**
     * Verify a submitted 2FA code.
     */
    public function verifyCode(User $user, string $clientSignature, string $code): bool
    {
        $record = TwoFactorCode::where('user_id', $user->id)
            ->where('client_signature', $clientSignature)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest('created_at')
            ->first();

        if (!$record) {
            return false;
        }

        if ($record->attempts >= 3) {
            $record->update(['used_at' => Carbon::now()]);
            return false;
        }

        if (!Hash::check($code, $record->code)) {
            $record->increment('attempts');
            return false;
        }

        $record->update(['used_at' => Carbon::now()]);

        // Reset rate limit counters on success
        Cache::forget("2fa_request_client_{$clientSignature}");
        Cache::forget("2fa_request_user_{$user->id}");

        return true;
    }

    /**
     * Mark all unused codes for this user+client as used.
     */
    public function invalidateCodes(User $user, string $clientSignature): void
    {
        TwoFactorCode::where('user_id', $user->id)
            ->where('client_signature', $clientSignature)
            ->whereNull('used_at')
            ->update(['used_at' => Carbon::now()]);
    }

    /**
     * Check rate limits and throw if exceeded.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    private function checkRateLimits(int $userId, string $clientSignature): void
    {
        $clientCount = Cache::get("2fa_request_client_{$clientSignature}", 0);
        $userCount   = Cache::get("2fa_request_user_{$userId}", 0);

        if ($clientCount >= $this->maxPerClient) {
            $delayKey = "2fa_delay_client_{$clientSignature}";
            if (Cache::has($delayKey)) {
                abort(429, "Too many requests. Please wait {$this->clientDelay} seconds.");
            }
            Cache::put($delayKey, true, Carbon::now()->addSeconds($this->clientDelay));
        }

        if ($userCount >= $this->maxPerUser) {
            $delayKey = "2fa_delay_user_{$userId}";
            if (Cache::has($delayKey)) {
                abort(429, "Too many requests. Please wait {$this->userDelay} seconds.");
            }
            Cache::put($delayKey, true, Carbon::now()->addSeconds($this->userDelay));
        }
    }
}