<?php

namespace Database\Seeders;

use App;
use App\Http\Services\Contracts\TenantServiceInterface;
use App\Models\Tenant;
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
            App::make(TenantServiceInterface::class)->createTenantSettings($tenant);
        }

        $this->seed($this::class);
    }
}
