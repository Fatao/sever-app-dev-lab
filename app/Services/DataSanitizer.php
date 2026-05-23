<?php

declare(strict_types=1);

namespace App\Services;

class DataSanitizer
{
    /**
     * Fields whose values should be masked.
     *
     * @var string[]
     */
    private const SENSITIVE_KEYS = [
        'password',
        'c_password',
        'token',
        'access_token',
        'refresh_token',
        'temp_token',
        'secret_key',
        'authorization',
    ];

    /**
     * Recursively mask sensitive fields in an array.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                // Mask regardless of whether value is string or array
                if (is_array($value)) {
                    $data[$key] = array_map(
                        fn($v) => str_repeat('*', strlen((string) $v)),
                        $value
                    );
                } else {
                    $data[$key] = str_repeat('*', strlen((string) $value));
                }
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }
}