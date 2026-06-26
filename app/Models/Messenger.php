<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Messenger extends Model
{
    protected $fillable = ['name', 'description', 'environment', 'token_env_var'];

    /**
     * Get all user connections for this messenger.
     */
    public function userMessengers(): HasMany
    {
        return $this->hasMany(UserMessenger::class);
    }
}
