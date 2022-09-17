<?php

namespace Database\Seeders;

use App\Enums\UserLevel;
use App\Models\Tenant;
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
            'Document' => [
                'Document: View List',
                'Document: Preview',
                'Document: Create',
                'Document: Edit',
                'Document: Delete',
                'Document: Download',
            ],
            'Settings' => [
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
            ],
            'User' => [
                'User: View List',
                'User: Create',
                'User: Edit User',
                'User: Delete User',
            ],
            'Role' => [
                'Role: View List',
                'Role: Create',
                'Role: Edit User',
                'Role: Delete User',
            ],
        ];

        $rolePermissions = [
            UserLevel::Superadmin => $permissions,
            UserLevel::Admin      => $permissions,
            UserLevel::Regular    => [
                'Document' => [
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
        $tenant = Tenant::first();

        foreach ($rolePermissions as $roleName => $permissionModules) {
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);

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
