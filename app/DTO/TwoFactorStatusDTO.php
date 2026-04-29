<?php

declare(strict_types=1);

namespace App\DTO;

class TwoFactorStatusDTO
{
    public function __construct(
        public readonly bool $twoFactorEnabled,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['two_factor_enabled' => $this->twoFactorEnabled];
    }
}