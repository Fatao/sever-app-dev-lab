<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserUpdated;
use App\Jobs\SendNotificationJob;

class SendUpdateNotification
{
    /**
     * Handle the UserUpdated event.
     */
    public function handle(UserUpdated $event): void
    {
        SendNotificationJob::dispatch(
            $event->user->id,
            'Ваши данные были изменены.',
            'user_updated',
        );
    }
}
