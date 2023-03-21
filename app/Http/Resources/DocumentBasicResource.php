<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentBasicResource extends JsonResource
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
            'id'                => $this->id,
            'series_id'         => $this->series_id,
            'filename'          => $this->file_name,
            'extension'         => strtolower($this->latest_media->extension),
            'size'              => $this->latest_media->human_readable_size,
            'mime_type'         => $this->latest_media->mime_type,
            'url'               => $this->cloud_url,
            'udfs'              => $this->formatted_udfs,
            'created_by'        => $this->formatted_created_by,
            'updated_by'        => $this->formatted_updated_by,
            'created_at'        => $this->formatted_created_at,
            'updated_at'        => $this->formatted_updated_at,
            'match'             => $this->match,
            'has_user_metadata' => $this->has_user_metadata
        ];
    }
}
