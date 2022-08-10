<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Traits\Seedable;
use Hash;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
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
            $this->createTenantSuperadmin($tenant);
            $this->createTenantAdmin($tenant);
        }

        $this->seed($this::class);
    }

    private function createTenantSuperadmin($tenant) 
    {
        $username = 'superadmin';
        $email = 'superadmin@dms.com';
        $password = 'SUP3R-@DMIN+';

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        $user->userInfo()->create([
            'first_name' => 'superadmin',
            'last_name' => 'dms',
            'tenant_id' => $tenant->id
        ]);

        $user->assignRole('superadmin');
    }

    private function createTenantAdmin($tenant) 
    {
        $username = 'admin';
        $email = 'admin@dms.com';
        $password = '@DMIN+';

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        $user->userInfo()->create([
            'first_name' => 'admin',
            'last_name' => 'dms',
            'tenant_id' => $tenant->id
        ]);

        $user->assignRole('admin');
    }
}
