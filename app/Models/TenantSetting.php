<?php

namespace App\Models;

use App\Traits\FilterByTenant;

class TenantSetting extends BaseModel
{
    use FilterByTenant;

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
        'comments',
        'created_by',
        'updated_by',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getValueAttribute($value)
    {
        if ($this->type === 'number') {
            $value = (int) $value;
        }

        return $value;
    }
}
