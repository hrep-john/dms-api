<?php

namespace Database\Seeders;

use App;
use App\Enums\UserLevel;
use App\Http\Services\Contracts\RoleServiceInterface;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use App\Traits\Seedable;
use Artisan;

class ProjectInitSeeder extends Seeder
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

        // Reset Meilisearch Indexing
        $this->resetIndexing("App\\\\Models\\\\Document");
        $this->resetIndexing("App\\\\Models\\\\DocumentEntityMetadata");

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createRolePermissions();

        $this->seed($this::class);
    }

    private function createRolePermissions()
    {
        foreach(Tenant::cursor() as $tenant) {
            App::make(RoleServiceInterface::class)->createNewTenantRolesAndPermissions($tenant->id);
        }
    }

    private function resetIndexing($model)
    {
        Artisan::call(sprintf('scout:flush "%s"', $model));
    }
}
