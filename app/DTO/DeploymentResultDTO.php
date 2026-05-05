<?php

declare(strict_types=1);

namespace App\DTO;

class DeploymentResultDTO
{
    public function __construct(
        public readonly string  $status,
        public readonly string  $message,
        public readonly ?string $output = null,
    ) {}

    /**
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status'  => $this->status,
            'message' => $this->message,
            'output'  => $this->output,
        ];
    }
}