<?php

namespace App\Http\Resources;

use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogBasicResource extends JsonResource
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
            'date'                          => Carbon::parse($this->updated_at)->format('Y-m-d'),
            'time'                          => Carbon::parse($this->updated_at)->format('g:i A'),
            'event'                         => $this->event,
            'filename'                      => Document::find(request('id'))->file_name,
            'full_name'                     => $this->user->user_info->full_name,
            'profile_picture_url'           => $this->user->user_info->profile_picture_url ?? '',
            'old_values'                    => $this->old_values,
            'new_values'                    => $this->new_values,
            'changes'                       => $this->getChanges($this->old_values['user_defined_field'] ?? [], $this->new_values['user_defined_field'] ?? []),
        ];
    }

    private function getChanges($oldValues, $newValues) 
    {
        $changes = [];

        foreach ($newValues as $field => $value) {
            $changes[] = [
                'field' => $field,
                'new_value' => $value,
                'old_value' => $oldValues[$field] ?? '',
            ];
        }

        return $changes;
    }
}
