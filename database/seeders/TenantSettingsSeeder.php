<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Traits\Seedable;
use Illuminate\Database\Seeder;

class TenantSettingsSeeder extends Seeder
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

        foreach(Tenant::withoutGlobalScopes()->cursor() as $tenant) {
            $settings = [
                [
                    'tenant_id' => $tenant->id,
                    'key' => 'login.background.image',
                    'value' => '/@src/assets/illustrations/login/background-light.svg?format=web',
                    'type' => 'string',
                    'comments' => 'Login Background Image'
                ]
            ];

            foreach($settings as $setting) {
                TenantSetting::create($setting);
            }
        }

        $this->seed($this::class);
    }
}
