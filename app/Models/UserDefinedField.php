<?php

namespace App\Models;

use App;
use App\Enums\UserDefinedFieldSource;
use App\Http\Resources\UserListResource;
use App\Http\Services\Contracts\UserServiceInterface;
use App\Traits\FilterUdfsByTenant;

class UserDefinedField extends BaseModel
{
    use FilterUdfsByTenant;

    protected $fillable = [
        'tenant_id',
        'entitable_type',
        'label',
        'key',
        'section',
        'type',
        'visible',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function getFormattedSettingsAttribute()
    {
        $settings = JSON_DECODE($this->settings, true);
        $options = [];

        if (count($settings) > 0) {
            switch ($settings['source']) {
                case UserDefinedFieldSource::Users: 
                    $options = UserListResource::collection(App::make(UserServiceInterface::class)->all(false));
                    break;
                case UserDefinedFieldSource::Custom:
                    $options = $this->getCustomDataSource($settings);
                    break;
            }
        }

        $settings['options'] = $options;

        return $settings;
    }

    private function getCustomDataSource($settings) 
    {
        $options = [];

        foreach ($settings['data'] as $item) {
            $options[] = [
                'id' => $item['id'],
                'label' => $item['label']
            ];
        }

        return $options;
    }
}
