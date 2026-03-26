<?php
declare(strict_types=1);

namespace App\DTO;

final class RoleCollectionDTO
{
    /**
     * @param RoleDTO[] $roles
     */
    public function __construct(
        public readonly array $roles,
        public readonly int $total,
    ) {}

    /**
     * Convert DTO to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'data'  => array_map(fn(RoleDTO $role) => $role->toArray(), $this->roles),
            'total' => $this->total,
        ];
    }
}