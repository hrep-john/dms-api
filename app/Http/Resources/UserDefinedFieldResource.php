<?php

namespace App\Http\Resources;

use App\Enums\UserDefinedFieldType;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDefinedFieldResource extends JsonResource
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
            'entitable_type'        => $this->entitable_type,
            'formatted_entity'      => Str::of($this->entitable_type)->after('App\\Models\\'),
            'label'                 => $this->label,
            'key'                   => $this->key,
            'visible'               => $this->visible,
            'type'                  => $this->type,
            'formatted_type'        => UserDefinedFieldType::getKey($this->type),
            'settings'              => $this->formatted_settings,
            'created_by'            => $this->formatted_created_by,
            'updated_by'            => $this->formatted_updated_by,
            'created_at'            => $this->formatted_created_at,
            'updated_at'            => $this->formatted_updated_at,
        ];
    }
}
