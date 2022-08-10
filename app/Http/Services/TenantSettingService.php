<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\TenantSettingServiceInterface;
use App\Models\TenantSetting;
use Illuminate\Database\Eloquent\Collection;

class TenantSettingService extends BaseService implements TenantSettingServiceInterface
{
    /**
    * TenantSettingService constructor.
    *
    * @param TenantSetting $model
    */
    public function __construct(TenantSetting $model)
    {
        parent::__construct($model);
    }

    /**
    * @param $id
    * @return Model
    */
    public function findByDomain(string $domain): ?Collection
    {
        $test = $this->model->whereHas('tenant', function($query) use ($domain) {
            return $query->where('tenants.domain', $domain);
        });

        return $test->get();
    }
}