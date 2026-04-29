<?php

declare(strict_types=1);

namespace App\DTO;

class TwoFactorRequestCodeDTO
{
    public function __construct(
        public readonly string $message,
        public readonly ?string $code = null, // only for testing/dev
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = ['message' => $this->message];

        if (!is_null($this->code)) {
            $data['code'] = $this->code; // emulated delivery
        }

        return $data;
    }
}