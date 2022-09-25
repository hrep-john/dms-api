<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\TenantServiceInterface;
use App\Models\Tenant;
use App\Models\User;
use Hash;

class TenantService extends BaseService implements TenantServiceInterface
{
    /**
    * TenantService constructor.
    *
    * @param Tenant $model
    */
    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }

    protected function afterStore($model, $attributes): void
    {
        $this->createTenantInitialFolder($model);
        $this->createTenantSuperadmin($model);
        $this->createTenantAdmin($model);
        $this->createTenantSettings($model);
    }

    private function createTenantInitialFolder($model) 
    {
        $model->folders()->create([
            'name' => $model->name
        ]);
    }

    private function createTenantSettings($model) 
    {
        $settings = [
            [
                'tenant_id' => $model->id,
                'key' => 'login.background.image',
                'value' => '/images/illustrations/login/background-light.svg?format=web',
                'type' => 'string',
                'comments' => 'Login Background Image',
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id
            ]
        ];

        $model->settings()->createMany($settings);
    }

    private function createTenantSuperadmin($model) 
    {
        $username = 'superadmin';
        $email = 'superadmin@dms.com';
        $password = 'SUP3R-@DMIN+';

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        $user->user_info()->create([
            'first_name' => 'superadmin',
            'last_name' => 'dms',
            'tenant_id' => $model->id
        ]);

        $user->assignRole('superadmin');
    }

    private function createTenantAdmin($model) 
    {
        $username = 'tenant-admin';
        $email = 'admin@dms.com';
        $password = '@DMIN+';

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        $user->user_info()->create([
            'first_name' => 'admin',
            'last_name' => 'dms',
            'tenant_id' => $model->id
        ]);

        $user->assignRole('admin');
    }
}