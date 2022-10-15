<?php

namespace App\Http\Services;

use App;
use App\Enums\UserLevel;
use App\Http\Services\Contracts\RoleServiceInterface;
use App\Http\Services\Contracts\TenantServiceInterface;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Models\UserInfo;
use Hash;

class TenantService extends BaseService implements TenantServiceInterface
{
    protected $configs = [
        // Application Theme
        [
            'label' => 'Logo',
            'key' => 'tenant-logo',
            'value' => '/default-logo.png',
            'comments' => 'Tenant Customization - App Theme - Logo'
        ],
        [
            'label' => 'Primary Theme Color',
            'key' => '--primary',
            'value' => '#41b883',
            'comments' => 'Tenant Customization - App Theme - Primary Theme Color'
        ],
        [
            'label' => 'Font Color',
            'key' => '--dark-text',
            'value' => '#283252',
            'comments' => 'Tenant Customization - App Theme - Font Color'
        ],
        [
            'label' => 'Background Color',
            'key' => '--background-color',
            'value' => '#f0f0f0',
            'comments' => 'Tenant Customization - App Theme - Background Color'
        ],
        // Sidebar Navigation
        [
            'label' => 'Font Color',
            'key' => '--main-sidebar-font-color',
            'value' => '#a9abac',
            'comments' => 'Tenant Customization - Main Sidebar - Font Color'
        ],
        [
            'label' => 'Background Color',
            'key' => '--main-sidebar-background-color',
            'value' => '#f9f9f9',
            'comments' => 'Tenant Customization - Main Sidebar - Background Color'
        ],
        // Login Page
        [
            'label' => 'Left Section - Placeholder Image',
            'key' => 'login-placeholder-image',
            'value' => '/images/illustrations/login/background-light.svg?format=web',
            'comments' => 'Tenant Customization - Login Page - Placeholder Image'
        ],
        [
            'label' => 'Left Section - Background Color',
            'key' => '--login-left-section-background-color',
            'value' => '#f5f6fa',
            'comments' => 'Tenant Customization - Login Page - Font Color'
        ],
        [
            'label' => 'Right Section - Background Color',
            'key' => '--login-right-section-background-color',
            'value' => '#ffffff',
            'comments' => 'Tenant Customization - Login Page - Background Color'
        ],
        // Forgot Password Page
        [
            'label' => 'Left Section - Background Color',
            'key' => '--forgot-password-left-section-background-color',
            'value' => '#ffffff',
            'comments' => 'Tenant Customization - Forgot Password Page - Font Color'
        ],
        [
            'label' => 'Right Section - Placeholder Image',
            'key' => 'forgot-password-placeholder-image',
            'value' => '/images/illustrations/forgot-password/background-dark.svg?format=webp',
            'comments' => 'Tenant Customization - Forgot Password Page - Placeholder Image'
        ],
        [
            'label' => 'Right Section - Background Color',
            'key' => '--forgot-password-right-section-background-color',
            'value' => '#f5f6fa',
            'comments' => 'Tenant Customization - Forgot Password Page - Background Color'
        ],
        // Reset Password Page
        [
            'label' => 'Left Section - Placeholder Image',
            'key' => 'reset-password-placeholder-image',
            'value' => '/images/illustrations/reset-password/background-dark.svg?format=webp',
            'comments' => 'Tenant Customization - Reset Password Page - Placeholder Image'
        ],
        [
            'label' => 'Left Section - Background Color',
            'key' => '--reset-password-left-section-background-color',
            'value' => '#ffffff',
            'comments' => 'Tenant Customization - Reset Password Page - Font Color'
        ],
        [
            'label' => 'Right Section - Background Color',
            'key' => '--reset-password-right-section-background-color',
            'value' => '#f5f6fa',
            'comments' => 'Tenant Customization - Reset Password Page - Background Color'
        ],
        [
            'label' => 'Document Series Prefix',
            'key' => 'tenant.document.series.id.prefix',
            'value' => 'DOC',
            'type' => 'string',
            'comments' => 'Tenant Document Series Id Prefix'
        ],
        [
            'label' => 'Document Series Counter',
            'key' => 'tenant.document.series.current.counter',
            'value' => 1,
            'type' => 'number',
            'comments' => 'Tenant Document Series Current Counter'
        ],
        [
            'label' => 'Document Series Precision',
            'key' => 'tenant.document.series.counter.length',
            'value' => 5,
            'type' => 'number',
            'comments' => 'Tenant Document Series Counter Length'
        ],
    ];

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
        App::make(RoleServiceInterface::class)->createNewTenantRolesAndPermissions($model->id);

        $this->createTenantInitialFolder($model);
        $this->createTenantSuperadmin($model);
        $this->createTenantAdmin($model);
        $this->createTenantSettings($model);
    }

    public function createTenantSettings($model): void
    {
        $tenantSettings = collect($this->getTenantSettings($model))->chunk(250);

        foreach($tenantSettings as $settings) {
            TenantSetting::insert($settings->toArray());
        }
    }

    protected function getTenantSettings($tenant) 
    {
        $tenantSettings = [];

        $superadmin = User::withoutGlobalScopes()
            ->whereHas('user_info', function ($query) use ($tenant) {
                return $query->where('tenant_id', $tenant->id);
            })
            ->first();

        foreach ($this->configs as $config) {
            $tenantSettings[] = [
                'tenant_id' => $tenant->id,
                'label' => $config['label'],
                'key' => $config['key'],
                'value' => $config['value'],
                'type' => 'string',
                'comments' => $config['comments'],
                'created_by' => $superadmin->id,
                'updated_by' => $superadmin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $tenantSettings;
    }

    private function createTenantInitialFolder($model) 
    {
        $model->folders()->create([
            'name' => $model->name
        ]);
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

        $role = Role::withoutGlobalScopes()
            ->where('name', UserLevel::Superadmin)
            ->where('tenant_id', $model->id)
            ->first();

        $user->roles()->attach($role->id);
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

        $role = Role::withoutGlobalScopes()
            ->where('name', UserLevel::Admin)
            ->where('tenant_id', $model->id)
            ->first();

        $user->roles()->attach($role->id);
    }
}