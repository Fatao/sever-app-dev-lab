<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    /**
     * @param User $user The user who logged in.
     */
    public function __construct(public readonly User $user) {}
}
