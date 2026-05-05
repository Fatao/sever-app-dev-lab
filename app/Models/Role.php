<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
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
        static::creating(function (self $role) {
            $role->created_by = auth()->id() ?? 1;
        });

        static::deleting(function (self $role) {
            if (!$role->isForceDeleting()) {
                $role->deleted_by = auth()->id() ?? 1;
            }
        });

        static::restoring(function (self $role) {
            $role->deleted_by = null;
        });
    }

    /**
     * Get the permissions associated with the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
            'role_id',
            'permission_id'
        )
        ->using(PermissionRole::class)
        ->withPivot('created_at', 'created_by', 'deleted_at', 'deleted_by')
        ->wherePivotNull('deleted_at')
        ->whereNull('permissions.deleted_at');
    }

    /**
     * Get the users associated with the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'role_user',
            'role_id',
            'user_id'
        )
        ->using(UserRole::class)
        ->withPivot('created_at', 'created_by', 'deleted_at', 'deleted_by')
        ->wherePivotNull('deleted_at')
        ->whereNull('users.deleted_at');
    }
}