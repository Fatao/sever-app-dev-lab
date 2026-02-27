<?php

namespace App\DTO;

final class ServerInfoDTO
{
    public function __construct(
        public readonly string $phpVersion,
        public readonly string $serverSoftware
    ) {}

    public function toArray(): array
    {
        return [
            'php_version' => $this->phpVersion,
            'server_software' => $this->serverSoftware,
        ];
    }
}