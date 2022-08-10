<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class DocumentEntityMetadata extends BaseModel
{
    use HasFactory, Searchable;

    protected $table = 'document_entity_metadata';

    public $asYouType = true;

    public function toSearchableArray() 
    {
        $document = $this
            ->with('document')
            ->where('id', '=', $this->id)
            ->first()
            ->toArray();

        return $document;
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
