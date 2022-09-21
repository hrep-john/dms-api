<?php

namespace App\Models;

class Tenant extends BaseModel
{
    protected $fillable = [
        'domain',
        'name',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function user_info()
    {
        return $this->belongsToMany(UserInfo::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    public function settings()
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function custom_reports()
    {
        return $this->hasMany(ReportBuilder::class);
    }
}
