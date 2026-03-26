<?php
declare(strict_types=1);

namespace App\DTO;

final class RoleDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly string $created_at,
        public readonly int $created_by,
    ) {}

    /**
     * Convert DTO to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'created_at'  => $this->created_at,
            'created_by'  => $this->created_by,
        ];
    }

    /**
     * Create RoleDTO from Role model instance.
     */
    public static function fromModel(\App\Models\Role $role): self
    {
        return new self(
            id: $role->id,
            name: $role->name,
            slug: $role->slug,
            description: $role->description,
            created_at: $role->created_at->format('Y-m-d H:i:s'),
            created_by: $role->created_by,
        );
    }
}