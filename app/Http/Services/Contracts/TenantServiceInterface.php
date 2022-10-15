<?php

namespace App\Http\Services\Contracts;

interface TenantServiceInterface extends BaseServiceInterface
{
    public function createTenantSettings($model): void;
}