<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\RoleAssigned;
use App\Jobs\SendNotificationJob;

class SendRoleNotification
{
    /**
     * Handle the RoleAssigned event.
     */
    public function handle(RoleAssigned $event): void
    {
        SendNotificationJob::dispatch(
            $event->user->id,
            "Вам назначена роль: {$event->roleName}.",
            'role_assigned',
        );
    }
}
