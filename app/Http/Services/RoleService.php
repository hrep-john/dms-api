<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\RoleServiceInterface;
use Spatie\Permission\Models\Role;

class RoleService extends BaseService implements RoleServiceInterface
{
    /**
    * RoleService constructor.
    *
    * @param Role $model
    */
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }
}