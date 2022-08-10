<?php

namespace App\Http\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TenantSettingServiceInterface extends BaseServiceInterface
{
    /**
    * @param $domain
    * @return Model
    */
    public function findByDomain(string $domain): ?Collection;
}