<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Models\User;

interface TwoFactorServiceInterface
{
    public function generateCode(User $user, string $clientSignature): string;
    public function verifyCode(User $user, string $clientSignature, string $code): bool;
    public function invalidateCodes(User $user, string $clientSignature): void;
}