<?php
declare(strict_types=1);

namespace App\DTO;

final class PermissionCollectionDTO
{
    /**
     * @param PermissionDTO[] $permissions
     */
    public function __construct(
        public readonly array $permissions,
        public readonly int $total,
    ) {}

    /**
     * Convert DTO to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'data'  => array_map(fn(PermissionDTO $permission) => $permission->toArray(), $this->permissions),
            'total' => $this->total,
        ];
    }
}