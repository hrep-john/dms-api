<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Tenant;
use App\Traits\Seedable;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
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

        $tenants = Tenant::withoutGlobalScopes()->get();

        foreach($tenants as $tenant) {
            Folder::create([
                'tenant_id' => $tenant->id,
                'name' => $tenant->name
            ]);
        }

        $this->seed($this::class);
    }
}
