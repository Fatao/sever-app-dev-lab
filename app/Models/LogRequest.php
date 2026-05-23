<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogRequest extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'logs_requests';

    protected $fillable = [
        'full_url',
        'method',
        'controller_path',
        'controller_method',
        'request_body',
        'request_headers',
        'user_id',
        'ip_address',
        'user_agent',
        'response_status',
        'response_body',
        'response_headers',
        'called_at',
    ];

    protected $casts = [
        'request_body'     => 'array',
        'request_headers'  => 'array',
        'response_body'    => 'array',
        'response_headers' => 'array',
        'called_at'        => 'datetime',
        'created_at'       => 'datetime',
    ];
}