<?php

namespace App\DTO;

final class DatabaseInfoDTO
{
    public function __construct(
        public readonly string $driver,
        public readonly string $database,
        public readonly string $version
    ) {}

    public function toArray(): array
    {
        return [
            'driver' => $this->driver,
            'database' => $this->database,
            'version' => $this->version,
        ];
    }
}