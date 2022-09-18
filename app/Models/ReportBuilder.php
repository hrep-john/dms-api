<?php

namespace App\Models;

class ReportBuilder extends BaseModel
{
    protected $fillable = [
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
}
