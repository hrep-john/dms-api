<?php

namespace App\Http\Services;

use App\Enums\UserLevel;
use App\Http\Services\Contracts\RoleServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use App\Models\RoleHasPermission;
use DB;

class RoleService extends BaseService implements RoleServiceInterface
{
    protected $permissionModel;

    /**
     * RoleService constructor.
     *
     * @param Role $model
     */
    public function __construct(Role $model, Permission $permissionModel)
    {
        parent::__construct($model);
        $this->permissionModel = $permissionModel;
    }

    public function permissionList()
    {
        $tenantId = \App\Helpers\tenant();

        $permissions = $this->permissionModel->where('guard_name', 'api - tenant ' . $tenantId)->get();

        $list = [];

        foreach ($permissions->groupBy('module') as $module => $permissions) {
            $list[] = [
                'module' => $module,
                'permissions' => $permissions->pluck('name')
            ];
        }

        return $list;
    }

    public function createNewTenantRolesAndPermissions(int $tenantId)
    {
        $roles = [
            UserLevel::Superadmin,
            UserLevel::Admin,
            UserLevel::Regular
        ];

        $createdPermissions = [];

        foreach ($roles as $role) {
            $role = Role::create([
                'name' => $role,
                'guard_name' => 'api - tenant ' . $tenantId,
                'tenant_id' => $tenantId,
            ]);

            $permissionList = $this->getPermissionList();

            foreach ($permissionList as $module => $permissions) {
                foreach ($permissions as $permission) {
                    if (!in_array($permission, $createdPermissions)) {
                        $createdPermissions[] = $permission;

                        $permission = Permission::create([
                            'name' => $permission,
                            'module' => $module,
                            'guard_name' => 'api - tenant ' . $tenantId,
                        ]);
                    }
                }
            }

            $createdPermissionIds = Permission::where('guard_name', 'api - tenant ' . $tenantId)
                ->when($role->name === UserLevel::Regular, function ($query) use ($permissionList) {
                    return $query->whereIn('name', $permissionList['Document']);
                })
                ->pluck('id');

            foreach ($createdPermissionIds as $permissionId) {
                RoleHasPermission::insert([
                    'role_id' => $role->id,
                    'permission_id' => $permissionId
                ]);
            }
        }
    }

    protected function formatAttributes($attributes, $method): array
    {
        $tenantId = \App\Helpers\tenant();

        $newAttributes = [
            'name' => $attributes['name'],
            'guard_name' => 'api - tenant ' . $tenantId,
            'tenant_id' => $tenantId
        ];

        return $newAttributes;
    }

    protected function afterStore($model, $attributes): void
    {
        $permissions = Permission::whereIn('name', $attributes['permissions'])->where('guard_name', 'api - tenant ' . $model->tenant_id)->pluck('id');

        $model->syncPermissions($permissions);
    }

    protected function afterUpdated($model, $attributes): void
    {
        $model->syncPermissions($attributes['permissions']);
    }

    protected function beforeFiltering($model)
    {
        return $model->nonAdmin();
    }

    private function getPermissionList()
    {
        return [
            'Document' => [
                'Document: View List',
                'Document: Preview',
                'Document: Create',
                'Document: Edit',
                'Document: Delete',
                'Document: Download',
                'Document: Manage Transmittal',
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
                'Settings: View Signatories List',
                'Settings: Create Signatory',
                'Settings: Edit Signatory',
                'Settings: Delete Signatory',
            ],
            'User' => [
                'User: View List',
                'User: Create',
                'User: Edit User',
                'User: Delete User',
            ],
            'Tenant' => [
                'Tenant: View List',
                'Tenant: Create',
                'Tenant: Edit Tenant',
                'Tenant: Delete Tenant',
            ],
            'Role' => [
                'Role: View List',
                'Role: Create',
                'Role: Edit Role',
                'Role: Delete Role',
            ],
            'Report' => [
                'Report: View',
                'Report: Print',
            ]
        ];
    }
}
