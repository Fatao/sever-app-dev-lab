<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'messenger_id',
        'message',
        'status',
        'attempt',
        'response',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
