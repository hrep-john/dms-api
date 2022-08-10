<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'filename'              => $this->file_name,
            'extension'             => $this->latest_media->extension,
            'size'                  => $this->latest_media->human_readable_size,
            'mime_type'             => $this->latest_media->mime_type,
            'created_by'            => $this->formatted_created_by,
            'updated_by'            => $this->formatted_updated_by,
            'created_at'            => $this->formatted_created_at,
            'updated_at'            => $this->formatted_updated_at,
            'user_defined_field'    => $this->flatten_udfs
        ];
    }
}
