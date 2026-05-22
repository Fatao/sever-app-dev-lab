<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\Interfaces\AuditServiceInterface;

class UserObserver
{
    public function __construct(
        private readonly AuditServiceInterface $auditService,
    ) {}

    /**
     * Log when a user is created.
     */
    public function created(User $user): void
    {
        $this->auditService->log('user', $user->id, [], $this->toArray($user));
    }

    /**
     * Log when a user is updated.
     */
    public function updated(User $user): void
    {
        $this->auditService->log('user', $user->id, $this->toArray($user, true), $this->toArray($user));
    }

    /**
     * Log when a user is soft deleted.
     */
    public function deleted(User $user): void
    {
        $this->auditService->log('user', $user->id, $this->toArray($user), []);
    }

    /**
     * Log when a user is restored.
     */
    public function restored(User $user): void
    {
        $this->auditService->log('user', $user->id, [], $this->toArray($user));
    }

    /**
     * Log when a user is force deleted.
     */
    public function forceDeleted(User $user): void
    {
        $this->auditService->log('user', $user->id, $this->toArray($user), []);
    }

    /**
     * Get safe array representation (no password).
     *
     * @return array<string, mixed>
     */
    private function toArray(User $user, bool $original = false): array
    {
        $data = $original ? $user->getOriginal() : $user->getAttributes();
        unset($data['password'], $data['remember_token']);
        return $data;
    }
}