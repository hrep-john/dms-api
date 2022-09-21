<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\ReportBuilderServiceInterface;
use App\Models\ReportBuilder;
use Str;

class ReportBuilderService extends BaseService implements ReportBuilderServiceInterface
{
    /**
    * RoleService constructor.
    *
    * @param Role $model
    */
    public function __construct(ReportBuilder $model)
    {
        parent::__construct($model);
    }

    protected function formatAttributes($attributes): array
    {
        $attributes['slug'] = Str::slug($attributes['name']);
        $attributes['tenant_id'] = \App\Helpers\tenant();

        return $attributes;
    }
}