<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('slug', 'admin')->first();
        $user  = Role::where('slug', 'user')->first();
        $guest = Role::where('slug', 'guest')->first();

        // Admin gets all permissions
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            $this->attachPermission($admin->id, $permission->id);
        }

        // User gets limited permissions
        $userPermissions = ['get-list-user', 'read-user', 'update-user'];
        foreach ($userPermissions as $slug) {
            $permission = Permission::where('slug', $slug)->first();
            if ($permission) {
                $this->attachPermission($user->id, $permission->id);
            }
        }

        // Guest gets only get-list-user
        $guestPermission = Permission::where('slug', 'get-list-user')->first();
        if ($guestPermission) {
            $this->attachPermission($guest->id, $guestPermission->id);
        }
    }

    /**
     * Attach a permission to a role if not already attached.
     */
    private function attachPermission(int $roleId, int $permissionId): void
    {
        $exists = PermissionRole::where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            PermissionRole::create([
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
                'created_by'    => 1,
                'created_at'    => Carbon::now(),
            ]);
        }
    }
}