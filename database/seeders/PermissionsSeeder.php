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
                $name = ucfirst($action) . ' ' . ucfirst($entity);

                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name'        => $name,
                        'slug'        => $slug,
                        'description' => "Permission to {$action} {$entity}",
                        'created_by'  => 1,
                    ]
                );
            }
        }
    }
}