<?php

namespace App\Http\Services\Contracts;

interface UserServiceInterface extends BaseServiceInterface
{
    public function getSuperAdminUsers();
}