<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Traits\Seedable;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
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

        $tenants = [
            'demo.uat-dms.localhost:3000' => 'Demo Local',
            'demo.uat-dms.hrep.online' => 'Demo UAT HREP'
        ];

        foreach($tenants as $domain => $name) {
            Tenant::create([
                'domain' => $domain,
                'name' => $name
            ]);
        }

        $this->seed($this::class);
    }
}
