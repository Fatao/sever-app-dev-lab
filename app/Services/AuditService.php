<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChangeLog;
use App\Services\Interfaces\AuditServiceInterface;

class AuditService implements AuditServiceInterface
{
    /**
     * Save a change log entry to the database.
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
    ): void {
        ChangeLog::create([
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'before'      => $before,
            'after'       => $after,
            'created_by'  => auth()->id() ?? 1,
        ]);
    }
}