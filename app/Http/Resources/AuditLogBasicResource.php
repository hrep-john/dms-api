<?php

namespace App\Http\Resources;

use App\Models\Document;
use App\Models\User;
use App\Models\UserDefinedField;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;
use Str;

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
            'changes'                       => $this->getDiffTwoArrays($this->old_values['user_defined_field'] ?? '', $this->new_values['user_defined_field'] ?? '')
        ];
    }

    private function getDiffTwoArrays($old, $new)
    {
        $differences = [];

        $newData = json_decode($new, true);
        $oldData = json_decode($old, true);

        if (is_null($newData)) {
            return [];
        }

        foreach ($newData as $field => $value) {
            $data = null;

            if (!array_key_exists($field, $oldData)) {
                if (!is_null($value)) {
                    $data = $value;
                }
            } else if ($newData[$field] != $oldData[$field]) {
                $data = $value;
            }

            if (!is_null($data)) {
                $differences[] = [
                    'field' => $field,
                    'new_value' => $data ?? null,
                    'old_value' => $oldData[$field] ?? null
                ];
            }
        }

        return $this->formatUdfValues($differences);
    }

    private function formatUdfValues($differences) 
    {
        $formatted = [];

        foreach ($differences as $difference)
        {
            $udfSettings = collect(json_decode(UserDefinedField::where('key', $difference['field'])->first()->settings, false));

            $formatted[] = [
                'field' => $difference['field'],
                'old_value' => $this->getUdfLabel('old_value', $difference['old_value'], $udfSettings),
                'new_value' => $this->getUdfLabel('new_value', $difference['new_value'], $udfSettings)
            ];
        }

        return $formatted;
    }

    private function getUdfLabel($field, $value, $udfSettings)
    {
        $source = $this->getUdfSource($udfSettings);
        $label = $value;

        if ($source === 'users') {
            if (!is_null($value)) {
                $model = User::find($value);
                $label = $model->user_info->full_name;
            }
        } else if ($source === 'custom' && Str::isUuid($value)) {
            $data = $this->getUdfByUuid($udfSettings);
            $label = collect($data)->where('id', $value)->first()->label;
        }

        if (is_null($value) || $value == '') {
            $label = "null";
        }

        return $label;
    }

    private function getUdfByUuid($settings)
    {
        return $settings['data'] ?? [];
    }

    private function getUdfSource($settings)
    {
        return $settings['source'] ?? null;
    }
}
