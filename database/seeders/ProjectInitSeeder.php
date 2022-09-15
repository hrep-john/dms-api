<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Traits\Seedable;
use Artisan;

class ProjectInitSeeder extends Seeder
{
    use Seedable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->hasSeeder($this::class)) {
            return false;
        }

        // Reset Meilisearch Indexing
        $this->resetIndexing("App\\\\Models\\\\Document");
        $this->resetIndexing("App\\\\Models\\\\DocumentEntityMetadata");

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $rolePermissions = $this->getRolePermissions();

        $this->createRolePermissions($rolePermissions);
        $this->seed($this::class);
    }

    private function getRolePermissions() 
    {
        $permissions = [
            'user' => [
                'User: View List',
                'User: Create',
                'User: Edit User',
                'User: Delete User',
            ],
            'document' => [
                'Document: View List',
                'Document: Create',
                'Document: Edit',
                'Document: Delete',
                'Document: Download',
            ],
            'settings' => [
                'Settings: View Tenant Settings',
                'Settings: Edit Tenant Settings',
                'Settings: View Tenant Customization',
                'Settings: Edit Tenant Customization',
                'Settings: View User Defined Field List',
                'Settings: Create User Defined Field',
                'Settings: Edit User Defined Field',
                'Settings: Delete User Defined Field',
                'Settings: View Changelogs',
                'Settings: View Profile',
                'Settings: Edit Profile',
            ]
        ];

        $rolePermissions = [
            UserRole::Superadmin => $permissions,
            UserRole::Admin      => $permissions,
            UserRole::Encoder    => [
                'document' => [
                    'Document: View List',
                    'Document: Create',
                    'Document: Edit',
                    'Document: Download',
                ]
            ],
        ];

        return $rolePermissions;
    }

    private function createRolePermissions($rolePermissions)
    {
        $createdPermissions = [];
        foreach ($rolePermissions as $roleName => $permissionModules) {
            $role = Role::create(['name' => $roleName]);

            $permissions = [];

            foreach ($permissionModules as $module => $modulePermissions) {
                foreach ($modulePermissions as $permission) {
                    if (!in_array($permission, $createdPermissions)) {
                        Permission::create([
                            'name' => $permission,
                            'module' => $module
                        ]);

                        $createdPermissions[] = $permission;
                    }

                    $permissions[] = $permission;
                }
            }

            $role->givePermissionTo($permissions);
        }
    }

    private function resetIndexing($model)
    {
        Artisan::call(sprintf('scout:flush "%s"', $model));
    }
}
