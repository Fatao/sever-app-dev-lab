<?php

namespace App\Services\Interfaces;

use App\Models\User;
use App\DTO\AuthSuccessDTO;

interface TokenServiceInterface
{
    public function generateTokens(User $user): AuthSuccessDTO;

    public function validateAccessToken(string $token): ?User;

    public function refresh(string $refreshToken): AuthSuccessDTO;

    public function revoke(string $token): void;

    public function revokeAll(User $user): void;
}