<?php

declare(strict_types=1);

namespace App\DTO;

final class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly ?string $birthday,
        public readonly array $roles = [],   // array of RoleDTO
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'roles' => $this->roles,           // Important!
        ];
    }

    /**
     * Create UserDTO from User model
     */
    public static function fromModel(\App\Models\User $user): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday?->format('Y-m-d'),
            roles: $user->roles->map(fn($role) => RoleDTO::fromModel($role))->toArray(),
        );
    }
}