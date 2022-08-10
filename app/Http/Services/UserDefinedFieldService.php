<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\UserDefinedFieldServiceInterface;
use App\Models\UserDefinedField;
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

    protected function formatAttributes($attributes): array
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
}