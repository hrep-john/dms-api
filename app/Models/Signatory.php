<?php

namespace App\Models;

class Signatory extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'designation',
        'office',
        'created_by',
        'updated_by',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
