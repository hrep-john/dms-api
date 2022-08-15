<?php

namespace App\Http\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface TenantSettingServiceInterface extends BaseServiceInterface
{
    /**
    * @param $domain
    * @return Model
    */
    public function findByDomain(string $domain): ?Collection;
    public function sync(Model $model, array $settings);
    public function upload($file);
}