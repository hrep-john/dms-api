<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportBuilderBasicResource extends JsonResource
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
            'module'        => $this->module,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'updated_by'    => $this->formatted_updated_by,
            'updated_at'    => $this->formatted_updated_at,
        ];
    }
}
