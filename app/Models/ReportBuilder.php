<?php

namespace App\Models;

class ReportBuilder extends BaseModel
{
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
