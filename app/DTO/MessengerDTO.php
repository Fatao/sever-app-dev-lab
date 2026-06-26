<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\Messenger;

class MessengerDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly string  $environment,
    ) {}

    /**
     * Build DTO from model instance.
     */
    public static function fromModel(Messenger $messenger): self
    {
        return new self(
            id:          $messenger->id,
            name:        $messenger->name,
            description: $messenger->description,
            environment: $messenger->environment,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'environment' => $this->environment,
        ];
    }
}
