<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterUsersByTenant
{
    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope(function(Builder $builder) {
            $builder->whereHas('userInfo', function (Builder $query) {
                $tenant = \App\Helpers\tenant();

                if (!is_null($tenant)) {
                    $query->where('tenant_id', $tenant);
                }
            });
        });
    }
}