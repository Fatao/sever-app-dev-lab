<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'change_logs';

    /**
     * Disable updated_at — change_logs only has created_at.
     */
    public const UPDATED_AT = null;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'entity_type',
        'entity_id',
        'before',
        'after',
        'created_at',
        'created_by',
    ];

    /**
     * Cast JSON fields to arrays automatically.
     */
    protected $casts = [
        'before'     => 'array',
        'after'      => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who triggered this log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}