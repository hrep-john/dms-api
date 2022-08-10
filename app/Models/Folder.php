<?php

namespace App\Models;

class Folder extends BaseModel
{
    protected $fillable = [
        'parent_id',
        'tenant_id',
        'name',
        'created_by',
        'updated_by',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
