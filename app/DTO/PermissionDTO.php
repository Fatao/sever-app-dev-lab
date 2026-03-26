<?php
declare(strict_types=1);

namespace App\DTO;

final class PermissionDTO
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
     * Create PermissionDTO from Permission model instance.
     */
    public static function fromModel(\App\Models\Permission $permission): self
    {
        return new self(
            id: $permission->id,
            name: $permission->name,
            slug: $permission->slug,
            description: $permission->description,
            created_at: $permission->created_at->format('Y-m-d H:i:s'),
            created_by: $permission->created_by,
        );
    }
}