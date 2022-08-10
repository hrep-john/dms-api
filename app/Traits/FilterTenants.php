<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterTenants
{
    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope(function(Builder $builder) {
            $tenant = \App\Helpers\tenant();

            if (!is_null($tenant)) {
                $builder->where('id', $tenant);
            }
        });
    }
}