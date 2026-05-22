<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $permission) {
            $permission->created_by = auth()->id() ?? 1;
        });

        static::deleting(function (self $permission) {
            if (!$permission->isForceDeleting()) {
                $permission->deleted_by = auth()->id() ?? 1;
            }
        });

        static::restoring(function (self $permission) {
            $permission->deleted_by = null;
        });
    }

    /**
     * Get the roles associated with the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permission_role',
            'permission_id',
            'role_id'
        )
        ->using(PermissionRole::class)
        ->withPivot('created_at', 'created_by', 'deleted_at', 'deleted_by')
        ->wherePivotNull('deleted_at')
        ->whereNull('roles.deleted_at');
    }
}