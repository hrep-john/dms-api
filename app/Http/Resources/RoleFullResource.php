<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleFullResource extends JsonResource
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
            'name'              => $this->name,
            'permissions'       => $this->getAllPermissions()->pluck('name'),
            'created_at'        => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at'        => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'created_by'        => User::find($this->created_by)->user_info->full_name ?? '',
            'updated_by'        => User::find($this->updated_by)->user_info->full_name ?? '',
        ];
    }
}
