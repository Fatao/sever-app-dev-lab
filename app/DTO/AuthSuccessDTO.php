<?php
declare(strict_types=1);

namespace App\DTO;

use App\Models\User;

final class AuthSuccessDTO
{
    public function __construct(
        public readonly string $access_token,
        public readonly string $refresh_token,
        public readonly User $user,
    ) {}

    /**
     * Convert DTO to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'access_token'  => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'user'          => [
                'id'       => $this->user->id,
                'username' => $this->user->username,
                'email'    => $this->user->email,
                'birthday' => $this->user->birthday?->format('Y-m-d'),
            ],
        ];
    }
}