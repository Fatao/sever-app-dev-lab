<?php

declare(strict_types=1);

namespace App\DTO;

class LogRequestCollectionDTO
{
    /**
     * @param LogRequestListItemDTO[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int   $total,
        public readonly int   $currentPage,
        public readonly int   $lastPage,
        public readonly int   $perPage,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                fn(LogRequestListItemDTO $dto) => $dto->toArray(),
                $this->items
            ),
            'meta' => [
                'total'        => $this->total,
                'current_page' => $this->currentPage,
                'last_page'    => $this->lastPage,
                'per_page'     => $this->perPage,
            ],
        ];
    }
}