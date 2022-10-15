<?php

namespace App\Http\Services\Contracts;

interface RoleServiceInterface extends BaseServiceInterface
{
    public function permissionList();
    public function createNewTenantRolesAndPermissions(int $tenantId);
}