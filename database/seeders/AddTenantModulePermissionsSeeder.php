<?php

namespace Database\Seeders;

use App\Enums\UserLevel;
use App\Models\Tenant;
use App\Traits\Seedable;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class AddTenantModulePermissionsSeeder extends Seeder
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

        $tenants = Tenant::withoutGlobalScopes()->get();

        foreach($tenants as $tenant) {
            $permissions = $this->createTenantModulePermissions($tenant->id);
            $this->syncRolePermissions($permissions, $tenant->id, UserLevel::Superadmin);
            $this->syncRolePermissions($permissions, $tenant->id, UserLevel::Admin);
        }

        $this->seed($this::class);
    }

    private function createTenantModulePermissions($tenantId) 
    {
        $permissions = [];

        $tenantModulePermissions = [
            'Tenant: View List',
            'Tenant: Create',
            'Tenant: Edit Tenant',
            'Tenant: Delete Tenant',
        ];

        foreach ($tenantModulePermissions as $tenantModulePermission) {
            Permission::create([
                'name' => $tenantModulePermission,
                'module' => 'Tenant',
                'guard_name' => 'api - tenant ' . $tenantId,
            ]);

            $permissions[] = $tenantModulePermission;
        }

        return $permissions;
    }

    private function syncRolePermissions($permissions, $tenantId, $userLevel)
    {
        $role = Role::where('tenant_id', $tenantId)
            ->where('name', $userLevel)
            ->first();

        $role->syncPermissions($permissions);
    }
}
