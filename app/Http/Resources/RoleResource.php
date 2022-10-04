<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'total_permissions' => $this->permissions->count(),
            'updated_at'        => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'updated_by'        => User::find($this->updated_by)->user_info->full_name ?? '',
        ];
    }
}
