<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterDocumentsByTenant
{
    protected static function bootFilterDocumentsByTenant()
    {
        parent::boot();

        self::addGlobalScope(function(Builder $builder) {
            $builder->whereHas('folder', function (Builder $query) {
                $tenant = \App\Helpers\tenant();

                if (!is_null($tenant)) {
                    $query->where('tenant_id', $tenant);
                }
            });
        });

        self::addGlobalScope(function(Builder $builder) {
            $builder->whereHas('userAccess', function (Builder $query) {
                return $query->where('user_id', auth()->user()->id);
            });
        });
    }
}