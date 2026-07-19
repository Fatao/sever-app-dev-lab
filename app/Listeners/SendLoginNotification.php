<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Jobs\SendNotificationJob;

class SendLoginNotification
{
    /**
     * Handle the UserLoggedIn event.
     */
    public function handle(UserLoggedIn $event): void
    {
        SendNotificationJob::dispatch(
            $event->user->id,
            'Вы успешно авторизовались в системе.',
            'login',
        );
    }
}
