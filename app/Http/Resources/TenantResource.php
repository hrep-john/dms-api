<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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
            'id'            => $this->id,
            'domain'        => $this->domain,
            'name'          => $this->name,
            'created_by'    => $this->formatted_created_by,
            'updated_by'    => $this->formatted_updated_by,
            'created_at'    => $this->formatted_created_at,
            'updated_at'    => $this->formatted_updated_at,
        ];
    }
}
