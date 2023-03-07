<?php

namespace Database\Seeders;

use App\Traits\Seedable;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;

class UpdateDocumentsPermissionSeeder extends Seeder
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

        foreach (Tenant::cursor() as $tenant) {
            $guard_name = 'api - tenant ' . $tenant->id;

            Permission::create([
                'name' => 'Document: Manage Transmittal',
                'module' => 'Document',
                'guard_name' => $guard_name,
            ]);

            $this->givePermissionToAdmins('Document: Manage Transmittal', $guard_name);
        }

        $this->seed($this::class);
    }

    private function givePermissionToAdmins($permission, $guard_name)
    {
        $roles = Role::whereIn('name', ['superadmin', 'admin'])
            ->where('guard_name', $guard_name)
            ->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($permission);
        }
    }
}
