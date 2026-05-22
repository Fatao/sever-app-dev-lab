<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Role;
use App\Services\Interfaces\AuditServiceInterface;

class RoleObserver
{
    public function __construct(
        private readonly AuditServiceInterface $auditService,
    ) {}

    /** Log when a role is created. */
    public function created(Role $role): void
    {
        $this->auditService->log('role', $role->id, [], $role->getAttributes());
    }

    /** Log when a role is updated. */
    public function updated(Role $role): void
    {
        $this->auditService->log('role', $role->id, $role->getOriginal(), $role->getAttributes());
    }

    /** Log when a role is soft deleted. */
    public function deleted(Role $role): void
    {
        $this->auditService->log('role', $role->id, $role->getAttributes(), []);
    }

    /** Log when a role is restored. */
    public function restored(Role $role): void
    {
        $this->auditService->log('role', $role->id, [], $role->getAttributes());
    }

    /** Log when a role is force deleted. */
    public function forceDeleted(Role $role): void
    {
        $this->auditService->log('role', $role->id, $role->getAttributes(), []);
    }
}