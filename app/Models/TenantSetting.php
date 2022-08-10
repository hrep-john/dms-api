<?php

namespace App\Models;

class TenantSetting extends BaseModel
{
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
}
