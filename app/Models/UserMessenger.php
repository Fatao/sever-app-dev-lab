<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMessenger extends Model
{
    protected $table = 'user_messenger';

    protected $fillable = [
        'user_id',
        'messenger_id',
        'messenger_user_id',
        'is_verified',
        'verified_at',
        'notifications_enabled',
    ];

    protected $casts = [
        'is_verified'           => 'boolean',
        'verified_at'           => 'datetime',
        'notifications_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns this connection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messenger for this connection.
     */
    public function messenger(): BelongsTo
    {
        return $this->belongsTo(Messenger::class);
    }
}
