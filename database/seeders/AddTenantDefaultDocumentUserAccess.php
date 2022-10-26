<?php

namespace Database\Seeders;

use App\Enums\AllowUserAccess;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Traits\Seedable;
use Illuminate\Database\Seeder;

class AddTenantDefaultDocumentUserAccess extends Seeder
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
            $key = 'tenant.default.document.user.access';
            $count = TenantSetting::where('key', $key)
                ->where('tenant_id', $tenant->id)
                ->count();

            if ($count === 0) {
                TenantSetting::create([
                    'tenant_id' => $tenant->id,
                    'label' => 'Tenant Default Document User Access',
                    'key' => $key,
                    'value' => AllowUserAccess::NoDontAllow,
                    'type' => 'number',
                    'comments' => 'Tenant Default Document User Access'
                ]);
            }
        }

        $this->seed($this::class);
    }
}
