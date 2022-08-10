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
                'create.user',
                'read.user',
                'update.user',
                'delete.user',
                'fetch.users'
            ],
            'document' => [
                'create.file',
                'read.file',
                'update.file',
                'delete.file',
                'fetch.files'
            ],
        ];

        $rolePermissions = [
            UserRole::Superadmin => [...$permissions['user'], ...$permissions['document']],
            UserRole::Admin      => [...$permissions['user'], ...$permissions['document']],
            UserRole::Encoder    => [...$permissions['document']],
        ];

        return $rolePermissions;
    }

    private function createRolePermissions($rolePermissions)
    {
        $allPermissions = $rolePermissions[UserRole::Superadmin];

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach($rolePermissions as $role => $permissions) {
            Role::create(['name' => $role])->givePermissionTo($permissions);
        }
    }

    private function resetIndexing($model)
    {
        Artisan::call(sprintf('scout:flush "%s"', $model));
    }
}
