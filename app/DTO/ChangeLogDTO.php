<?php

declare(strict_types=1);

namespace App\DTO;

class ChangeLogDTO
{
    /**
     * @param array<string, array{old: mixed, new: mixed}> $changedFields
     */
    public function __construct(
        public readonly int    $id,
        public readonly string $entityType,
        public readonly int    $entityId,
        public readonly array  $changedFields,
        public readonly string $createdAt,
        public readonly int    $createdBy,
    ) {}

    /**
     * Build DTO from a ChangeLog model instance.
     */
    public static function fromModel(\App\Models\ChangeLog $log): self
    {
        $before = $log->before ?? [];
        $after  = $log->after  ?? [];

        $changedFields = [];
        $allKeys = array_unique(array_merge(array_keys($before), array_keys($after)));

        foreach ($allKeys as $key) {
            $oldVal = $before[$key] ?? null;
            $newVal = $after[$key]  ?? null;
            if ($oldVal !== $newVal) {
                $changedFields[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        return new self(
            id:            $log->id,
            entityType:    $log->entity_type,
            entityId:      $log->entity_id,
            changedFields: $changedFields,
            createdAt:     $log->created_at->toDateTimeString(),
            createdBy:     $log->created_by,
        );
    }

    /**
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'entity_type'    => $this->entityType,
            'entity_id'      => $this->entityId,
            'changed_fields' => $this->changedFields,
            'created_at'     => $this->createdAt,
            'created_by'     => $this->createdBy,
        ];
    }
}