<?php

namespace App\Http\Services;

use App\Enums\UserDefinedFieldType;
use App\Http\Services\Contracts\UserDefinedFieldServiceInterface;
use App\Models\User;
use App\Models\UserDefinedField;
use Carbon\Carbon;
use Str;
use Uuid;

class UserDefinedFieldService extends BaseService implements UserDefinedFieldServiceInterface
{
    /**
    * UserDefinedFieldService constructor.
    *
    * @param UserDefinedField $model
    */
    public function __construct(UserDefinedField $model)
    {
        parent::__construct($model);
    }

    protected function formatAttributes($attributes, $method): array
    {
        $parsedSettings = JSON_DECODE($attributes['settings'], true);

        if (array_key_exists('data', $parsedSettings)) {
            $parsedSettings['data'] = $this->formatUdfCustomData($parsedSettings['data']);
            $attributes['settings'] = JSON_ENCODE($parsedSettings);
        }

        return $attributes;
    }

    protected function formatUdfCustomData($data) 
    {
        $formatted = [];

        foreach ($data as $item) {
            $formatted[] = [
                "id"    => is_null($item['id']) ? Uuid::generate()->string : $item['id'],
                "label" => $item['label'] ?? ''
            ];
        }

        return $formatted;
    }

    public function formatUdfValues($udfs): Array
    {
        $formatted = [];

        foreach ($udfs as $key => $value)
        {
            $formatted[$key] = $this->getUdfLabel($value, $key);
        }

        return $formatted;
    }

    private function getUdfLabel($value, $key)
    {
        $label = $value;
        $udf = $this->model->where('key', $key)->first();

        if ($udf->type === UserDefinedFieldType::Date) {
            $value = $value/1000;
            $label = Carbon::parse($value)->format('Y-m-d');
        } else if ($udf->type === UserDefinedFieldType::Dropdown) {
            $udfSettings = collect(json_decode($udf->settings, false));
            $source = $udfSettings['source'] ?? null;

            if ($source === 'users') {
                if (!is_null($value)) {
                    $model = User::find($value);
                    $label = $model->user_info->full_name;
                }
            } else if ($source === 'custom' && Str::isUuid($value)) {
                $data = $udfSettings['data'] ?? [];
                $label = collect($data)->where('id', $value)->first()->label;
            }
        }

        if (is_null($value) || $value == '') {
            $label = "null";
        }

        return $label;
    }
}