<?php

namespace App\Models;

use App\Enums\UserLevel;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Scope a query to only exclude superadmin and admin roles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNonAdmin($query)
    {
        $query->whereNotIn('name', [
            UserLevel::Superadmin,
            UserLevel::Admin
        ]);
    }
}
