<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $entities = ['user', 'role', 'permission'];
        $actions  = ['get-list', 'read', 'create', 'update', 'delete', 'restore'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $slug = "{$action}-{$entity}";

                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name'        => ucfirst($action) . ' ' . ucfirst($entity),
                        'description' => "Permission to {$action} {$entity}",
                        'created_by'  => 1,
                    ]
                );
            }
        }

        // Story permissions for Lab 4
        $storyPermissions = [
            ['name' => 'Get Story User',       'slug' => 'get-story-user',       'description' => 'View user change history'],
            ['name' => 'Get Story Role',       'slug' => 'get-story-role',       'description' => 'View role change history'],
            ['name' => 'Get Story Permission', 'slug' => 'get-story-permission', 'description' => 'View permission change history'],
        ];

        foreach ($storyPermissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name'        => $perm['name'],
                    'description' => $perm['description'],
                    'created_by'  => 1,
                ]
            );
        }
    }
}