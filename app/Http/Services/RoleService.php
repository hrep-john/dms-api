<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\RoleServiceInterface;
use Arr;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

    public function permissionList()
    {
        $permissions = $this->permissionModel->all();

        $list = [];

        foreach ($permissions->groupBy('module') as $module => $permissions) {
            $list[] = [
                'module' => $module,
                'permissions' => $permissions->pluck('name')
            ];
        }

        return $list;
    }

    protected function formatAttributes($attributes): array
    {
        $newAttributes = [
            'name' => $attributes['name'],
            'guard_name' => 'web',
            'tenant_id' => \App\Helpers\tenant()
        ];

        return $newAttributes;
    }

    protected function afterStore($model, $attributes): void
    {
        $model->syncPermissions($attributes['permissions']);
    }

    protected function afterUpdated($model, $attributes): void
    {
        $model->syncPermissions($attributes['permissions']);
    }
}