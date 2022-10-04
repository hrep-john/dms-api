<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterDocumentsByUserAccess
{
    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope(function(Builder $builder) {
            $builder->whereHas('userAccess', function (Builder $query) {
                return $query->where('user_id', auth()->user()->id);
            });
        });
    }
}