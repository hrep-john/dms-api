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

    /**
    * @param array $attributes
    *
    * @return Model
    */
    public function store($attributes): Model
    {
        $that = $this;

        $newAttributes = array_merge($this->formatAttributes($attributes), [
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id
        ]);

        return $this->transaction(function() use ($newAttributes, $attributes, $that) {
            $model = $that->model->create($newAttributes);
            $that->afterStore($model, $attributes);

            return $this->model->withoutGlobalScopes()->find($model->id);
        });
    }

    /**
    * @param array $attributes
    * @param int $id
    *
    * @return Model
    */
    public function update(array $attributes, Model $model): Model
    {
        $that = $this;

        $newAttributes = array_merge($this->formatAttributes($attributes), [
            'updated_by' => auth()->user()->id,
        ]);

        return $this->transaction(function() use ($attributes, $newAttributes, $model, $that) {
            $model->update($newAttributes);
            $that->afterUpdated($model, $attributes);

            return $model;
        });
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

    protected function formatAttributes($attributes): array
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
        Logger('beforesyncc');
        $permissions = Permission::whereIn('name', $attributes['permissions'])->where('guard_name', 'api - tenant '.$model->tenant_id)->pluck('id');
        Logger($permissions);
        Logger($model);

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
            'Report' => [
                'Report: View',
                'Report: Print',
            ]
        ];
    }
}
