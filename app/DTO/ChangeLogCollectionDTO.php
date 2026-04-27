<?php

declare(strict_types=1);

namespace App\DTO;

class ChangeLogCollectionDTO
{
    /**
     * @param ChangeLogDTO[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int   $total,
    ) {}

    /**
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data'  => array_map(fn(ChangeLogDTO $dto) => $dto->toArray(), $this->items),
            'total' => $this->total,
        ];
    }
}