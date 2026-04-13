<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionRole extends Pivot
{
    use SoftDeletes;

    protected $table = 'permission_role';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permission_id',
        'created_by',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $pivot) {
            $pivot->created_at = $pivot->created_at ?? now();
            $pivot->created_by = $pivot->created_by ?? (auth()->id() ?? 1);
        });

        static::deleting(function (self $pivot) {
            if (!$pivot->isForceDeleting()) {
                $pivot->deleted_by = auth()->id() ?? 1;
            }
        });

        static::restoring(function (self $pivot) {
            $pivot->deleted_by = null;
        });
    }
}