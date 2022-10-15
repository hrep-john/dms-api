<?php

namespace App\Http\Services;

use App\Enums\UserLevel;
use App\Http\Services\Contracts\UserServiceInterface;
use App\Models\Role;
use App\Models\User;
use Hash;

class UserService extends BaseService implements UserServiceInterface
{
    /**
    * UserService constructor.
    *
    * @param User $model
    */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function paginate() 
    {
        $perPage = request()->get('per_page', 10);
        $filters = request()->get('filters', []);

        $builder = $this->model
            ->regular()
            ->leftJoin('user_infos', 'user_infos.user_id', 'users.id')
            ->leftJoin('tenants', 'tenants.id', 'user_infos.tenant_id')
            ->select('users.*');

        foreach($filters as $filter) {
            $filter = JSON_DECODE($filter);

            $builder = $builder->orWhere($filter->column, $filter->operator, $filter->value);
        }

        return $builder->orderBy('users.id')->paginate($perPage);
    }

    public function getSuperAdminUsers() 
    {
        return $this->model->whereIn('user_level', [
            UserLevel::Superadmin,
            UserLevel::Admin
        ])->pluck('id')->toArray();
    }

    protected function formatAttributes($attributes): array
    {
        if (isset($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        $attributes['user_level'] = UserLevel::Regular;
        $attributes['user_info']['profile_picture_url'] = '/images/avatars/regular-user.jpg';
        $attributes['user_info']['last_name'] = $attributes['user_info']['last_name'] ?? '';
        $attributes['user_info']['last_name'] = $attributes['user_info']['last_name'] ?? '';

        return $attributes;
    }

    protected function afterStore($model, $attributes): void
    {
        $model->user_info()->create($attributes['user_info']);
        $roles = Role::whereIn('name', $attributes['roles'])->pluck('id');
        $model->roles()->attach($roles);
    }

    protected function afterUpdated($model, $attributes): void
    {
        $model->user_info()->update($attributes['user_info']);
        $roles = Role::whereIn('name', $attributes['roles'])->pluck('id');
        $model->roles()->sync($roles);
    }

    protected function afterDelete($model): void
    {
        $model->user_info()->delete();
    }
}