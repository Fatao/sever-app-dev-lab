<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface AuditServiceInterface
{
    /**
     * Log a mutation of an entity.
     *
     * @param string               $entityType
     * @param int                  $entityId
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     */
    public function log(
        string $entityType,
        int    $entityId,
        array  $before,
        array  $after,
    ): void;
}