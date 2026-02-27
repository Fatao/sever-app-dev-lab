<?php

namespace App\DTO;

final class ClientInfoDTO
{
    public function __construct(
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}

    public function toArray(): array
    {
        return [
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}