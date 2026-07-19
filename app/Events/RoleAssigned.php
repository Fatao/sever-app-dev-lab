<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use Dispatchable, SerializesModels;

    /**
     * @param User   $user     The user who received the role.
     * @param string $roleName The name of the assigned role.
     */
    public function __construct(
        public readonly User   $user,
        public readonly string $roleName,
    ) {}
}
