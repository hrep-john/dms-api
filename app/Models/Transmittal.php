<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transmittal extends BaseModel
{
    protected $fillable = [
        'document_id',
        'transmittal_url',
        'created_by',
        'updated_by',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
