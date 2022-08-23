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
                    'key' => 'tenant.document.series.id.prefix',
                    'value' => 'DOC',
                    'type' => 'string',
                    'comments' => 'Tenant Document Series Id Prefix'
                ],
                [
                    'tenant_id' => $tenant->id,
                    'key' => 'tenant.document.series.current.counter',
                    'value' => 1,
                    'type' => 'number',
                    'comments' => 'Tenant Document Series Current Counter'
                ],
                [
                    'tenant_id' => $tenant->id,
                    'key' => 'tenant.document.series.counter.length',
                    'value' => 5,
                    'type' => 'number',
                    'comments' => 'Tenant Document Series Counter Length'
                ],
            ];

            foreach($settings as $setting) {
                TenantSetting::create($setting);
            }
        }

        $this->seed($this::class);
    }
}
