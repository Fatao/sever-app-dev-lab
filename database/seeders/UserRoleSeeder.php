<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole  = Role::where('slug', 'user')->first();

        // First user gets admin role
        $firstUser = User::first();
        if ($firstUser && $adminRole) {
            $this->attachRole($firstUser->id, $adminRole->id);
        }

        // All other users get user role
        $otherUsers = User::skip(1)->take(PHP_INT_MAX)->get();
        foreach ($otherUsers as $user) {
            if ($userRole) {
                $this->attachRole($user->id, $userRole->id);
            }
        }
    }

    /**
     * Attach a role to a user if not already attached.
     */
    private function attachRole(int $userId, int $roleId): void
    {
        $exists = UserRole::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            UserRole::create([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ]);
        }
    }
}