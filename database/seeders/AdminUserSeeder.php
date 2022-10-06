<?php

namespace Database\Seeders;

use App\Enums\UserLevel;
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
            $this->createRegular($tenant);
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

        $user->user_info()->create([
            'first_name' => 'superadmin',
            'last_name' => 'user',
            'profile_picture_url' => '/images/avatars/superadmin-user.svg',
            'tenant_id' => $tenant->id
        ]);

        $user->assignRole(UserLevel::Superadmin);
    }

    private function createTenantAdmin($tenant) 
    {
        $username = 'tenant-admin';
        $email = 'admin@dms.com';
        $password = '@DMIN+';

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        $user->user_info()->create([
            'first_name' => 'tenant-admin',
            'last_name' => 'user',
            'profile_picture_url' => '/images/avatars/superadmin-user.svg',
            'tenant_id' => $tenant->id
        ]);

        $user->assignRole(UserLevel::Admin);
    }

    private function createRegular($tenant) 
    {
        $username = 'regular';
        $email = 'admin@dms.com';
        $password = '@DMIN+';

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        $user->user_info()->create([
            'first_name' => 'regular',
            'last_name' => 'user',
            'profile_picture_url' => '/images/avatars/regular-user.jpg',
            'tenant_id' => $tenant->id
        ]);

        $user->assignRole(UserLevel::Regular);
    }
}
