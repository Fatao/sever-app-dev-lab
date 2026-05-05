<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'description' => 'Administrator with full access',
                'created_by'  => 1,
            ],
            [
                'name'        => 'User',
                'slug'        => 'user',
                'description' => 'Regular user with limited access',
                'created_by'  => 1,
            ],
            [
                'name'        => 'Guest',
                'slug'        => 'guest',
                'description' => 'Guest with minimal access',
                'created_by'  => 1,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}