<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterDocuments
{
    protected static function boot()
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
            $userId = auth()->user()->id ?? null;

            if (!is_null($userId)) {
                $builder->whereHas('userAccess', function (Builder $query) use ($userId) {
                    return $query->where('user_id', $userId);
                });
            }
        });
    }
}