<?php
declare(strict_types=1);

namespace App\DTO;

final class UserDTO
{
    /**
     * @param RoleDTO[] $roles
     */
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $birthday,
        public readonly array $roles = [],
    ) {}

    /**
     * Convert DTO to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'username' => $this->username,
            'email'    => $this->email,
            'birthday' => $this->birthday,
            'roles'    => array_map(fn(RoleDTO $role) => $role->toArray(), $this->roles),
        ];
    }
}