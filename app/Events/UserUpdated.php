<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * @param User $user The user whose data was updated.
     */
    public function __construct(public readonly User $user) {}
}
