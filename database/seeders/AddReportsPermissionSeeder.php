<?php

namespace Database\Seeders;

use App\Traits\Seedable;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;

class AddReportsPermissionSeeder extends Seeder
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

        $permissions = [
            'Report: View',
            'Report: Print',
        ];

        foreach (Tenant::cursor() as $tenant) {
            foreach ($permissions as $permission) {
                Permission::create([
                    'name' => $permission,
                    'module' => 'Report',
                ]);

                $this->givePermissionToAdmins($permission);
            }
        }

        $this->seed($this::class);
    }

    private function givePermissionToAdmins($permission)
    {
        $roles = Role::whereIn('name', ['superadmin', 'admin'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($permission);
        }
    }
}
