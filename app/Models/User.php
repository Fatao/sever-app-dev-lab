<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'birthday',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
        ];
    }

    /**
     * Get the roles associated with the user (many-to-many with soft deletes)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->using(UserRole::class)
            ->withPivot('created_at', 'created_by', 'deleted_at', 'deleted_by')
            ->wherePivotNull('deleted_at')
            ->whereNull('roles.deleted_at');
    }

    /**
     * Check if user has a specific permission through any of his roles
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }
}