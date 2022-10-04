<?php

namespace App\Models;

use App\Traits\FilterByTenant;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ReportBuilder extends BaseModel implements HasMedia
{
    use FilterByTenant, InteractsWithMedia;

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
