<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'type',
        'expires_at',
        'revoked',
        'last_used_at',
        'ip_address',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
        'revoked'      => 'boolean',
    ];

    /**
     * Get the user that owns the token.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}