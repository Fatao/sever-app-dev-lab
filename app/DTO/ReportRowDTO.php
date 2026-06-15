<?php

declare(strict_types=1);

namespace App\DTO;

class ReportRowDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly int     $count,
        public readonly ?string $lastOperation,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'count'          => $this->count,
            'last_operation' => $this->lastOperation,
        ];
    }
}
