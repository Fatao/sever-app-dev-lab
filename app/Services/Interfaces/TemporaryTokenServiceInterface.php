<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Models\User;

interface TemporaryTokenServiceInterface
{
    public function generate(User $user, string $clientSignature): string;
    public function validate(string $token, string $clientSignature): ?int;
    public function invalidate(string $token): void;
}