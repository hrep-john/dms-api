<?php

namespace App\Models;

use App\Traits\FilterByTenant;

class ReportBuilder extends BaseModel
{
    use FilterByTenant;

    protected $fillable = [
        'tenant_id',
        'module',
        'name',
        'slug',
        'format',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'format' => 'json',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
