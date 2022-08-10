<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Laravel\Scout\Searchable;

class DocumentDetailMetadata extends BaseModel
{
    use HasFactory;

    protected $table = 'document_detail_metadata';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
