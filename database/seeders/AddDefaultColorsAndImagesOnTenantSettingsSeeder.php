<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Traits\Seedable;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\UserInfo;

class AddDefaultColorsAndImagesOnTenantSettingsSeeder extends Seeder
{
    use Seedable;

    protected $configs = [
        // Application Theme
        [
            'label' => 'Logo',
            'key' => 'tenant-logo',
            'value' => '/@src/assets/illustrations/login/background-light.svg?format=web',
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
            'value' => '/@src/assets/illustrations/login/background-light.svg?format=web',
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
            'value' => '/@src/assets/illustrations/forgot-password/background-dark.svg?format=webp',
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
            'value' => '/@src/assets/illustrations/reset-password/background-dark.svg?format=webp',
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
    ];

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

        $tenantSettings = collect($this->getTenantSettings())->chunk(250);

        foreach($tenantSettings as $settings) {
            TenantSetting::insert($settings->toArray());
        }

        $this->seed($this::class);
    }

    protected function getTenantSettings() 
    {
        $tenantSettings = [];

        foreach(Tenant::withoutGlobalScopes()->cursor() as $tenant) {
            $superadmin = UserInfo::where('tenant_id', $tenant->id)->first()->user;

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
        }

        return $tenantSettings;
    }
}
