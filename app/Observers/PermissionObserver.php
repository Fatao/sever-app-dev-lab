<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Permission;
use App\Services\Interfaces\AuditServiceInterface;

class PermissionObserver
{
    public function __construct(
        private readonly AuditServiceInterface $auditService,
    ) {}

    /** Log when a permission is created. */
    public function created(Permission $permission): void
    {
        $this->auditService->log('permission', $permission->id, [], $permission->getAttributes());
    }

    /** Log when a permission is updated. */
    public function updated(Permission $permission): void
    {
        $this->auditService->log('permission', $permission->id, $permission->getOriginal(), $permission->getAttributes());
    }

    /** Log when a permission is soft deleted. */
    public function deleted(Permission $permission): void
    {
        $this->auditService->log('permission', $permission->id, $permission->getAttributes(), []);
    }

    /** Log when a permission is restored. */
    public function restored(Permission $permission): void
    {
        $this->auditService->log('permission', $permission->id, [], $permission->getAttributes());
    }

    /** Log when a permission is force deleted. */
    public function forceDeleted(Permission $permission): void
    {
        $this->auditService->log('permission', $permission->id, $permission->getAttributes(), []);
    }
}