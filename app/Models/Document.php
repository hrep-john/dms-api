<?php

namespace App\Models;

use App;
use App\Http\Services\Contracts\UserDefinedFieldServiceInterface;
use App\Http\Services\Contracts\UserServiceInterface;
use App\Traits\FilterDocuments;
use Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends BaseModel implements HasMedia
{
    use HasFactory, Searchable, FilterDocuments, InteractsWithMedia;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'folder_id',
        'series_id',
        'file_name',
        'user_defined_field',
        'allow_user_access',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'user_defined_field' => 'string',
    ];

    protected $appends = [
        'formatted_udfs',
        'file_extension',
        'file_size',
        'tenant_id',
        'formatted_updated_at',
        'formatted_detail_metadata',
        'user_access',
        'has_user_metadata'
    ];

    public $asYouType = true;

    public function toSearchableArray() 
    {
        $document = $this
            ->where('id', '=', $this->id)
            ->first();

        if (!is_null($document)) {
            return Arr::only($document->toArray(), [
                'id',
                'tenant_id',
                'folder_id',
                'user_defined_field',
                'created_by',
                'updated_by',
                'formatted_udfs',
                'formatted_updated_at',
                'formatted_detail_metadata',
                'series_id',
                'file_name',
                'file_extension',
                'file_size',
                'user_access',
                'has_user_metadata'
            ]);
        }
    }

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'new_values.user_defined_field')) {
            $oldValues = JSON_DECODE($data['old_values']['user_defined_field'] ?? '', true) ?? [];
            $newValues = JSON_DECODE($data['new_values']['user_defined_field'] ?? '', true) ?? [];
            $diffNewValues = array_diff($newValues, $oldValues);

            if (count($diffNewValues) > 0) {
                $keys = array_keys($diffNewValues);
                $diffOldValues = Arr::only($oldValues, $keys);

                $data['old_values']['user_defined_field'] = App::make(UserDefinedFieldServiceInterface::class)->formatUdfValues($diffOldValues);
                $data['new_values']['user_defined_field'] = App::make(UserDefinedFieldServiceInterface::class)->formatUdfValues($diffNewValues);
            } else {
                $data['old_values']['user_defined_field'] = [];
                $data['new_values']['user_defined_field'] = [];
            }
        }

        return $data;
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function userAccess()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function detailMetadata()
    {
        return $this->hasMany(DocumentDetailMetadata::class);
    }

    public function entityMetadata()
    {
        return $this->hasMany(DocumentEntityMetadata::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function getFormattedUdfsAttribute() 
    {
        $tenantId = $this->tenant_id;
        Logger('tenantId');
        Logger(GETTYPE($tenantId));
        Logger($tenantId);
        $udfs = $this->flatten_udfs;
        $udfCollection = UserDefinedField::when(!is_null($tenantId), function ($query) use ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        })->get(['key', 'settings']);

        foreach ($udfs as $key => $value) {
            if (!empty($value)) {
                Logger('udfCollection');
                Logger($udfCollection);
                Logger('key');
                Logger($key);
                $settings = $udfCollection->where('key', $key)->first()->settings;
                $settings = JSON_DECODE($settings, true);

                if (count($settings) > 0) {
                    $udfs[$key] = $this->getUdfCustomLabel($settings, $value);
                }
            }
        }

        return $udfs;
    }

    public function getFormattedDetailMetadataAttribute() 
    {
        return $this->detailMetadata->pluck('text')->join(' ');
    }

    public function getFileExtensionAttribute() 
    {
        return $this->latest_media->extension ?? '';
    }

    public function getFileSizeAttribute() 
    {
        return $this->latest_media->size ?? '';
    }

    public function getTenantIdAttribute() 
    {
        return $this->folder->tenant_id ?? null;
    }

    public function getUserAccessAttribute() 
    {
        return $this->userAccess()->pluck('users.id');
    }

    public function getHasUserMetadataAttribute() 
    {
        $flag = false;

        $udfs = array_values($this->formatted_udfs);

        foreach ($udfs as $udf) {
            if (!is_null($udf)) {
                $flag = true;
            }
        }

        return $flag;
    }

    private function getUdfCustomLabel($settings, $value) 
    {
        $label = '';
        Logger('getUdfCustomLabel');
        Logger('settings');
        Logger($settings);
        Logger('value');
        Logger($value);

        if ($settings['source'] === 'custom') {
            $data = collect($settings['data'])->where('id', $value)->first();
            $label = $data['label'];
        } else if ($settings['source'] === 'users') {
            Logger($settings);
            Logger($value);
            $label = App::make(UserServiceInterface::class)->find($value)->user_info->full_name;
        }

        return $label;
    }

    public function getFlattenUdfsAttribute()
    {
        $udfs = App::make(UserDefinedFieldServiceInterface::class)->all(false);

        $flattenData = [];

        $currentValue = JSON_DECODE($this->user_defined_field, true);

        foreach($udfs as $udf) {
            $flattenData[$udf->key] = $currentValue[$udf->key] ?? null;
        }

        return $flattenData;
    }

    public function getLatestMediaAttribute()
    {
        return $this->getMedia('files')->last();
    }

    public function getCloudUrlAttribute()
    {
        $url = $this->latest_media->getFullUrl();

        $start = 0;
        $end = strpos($url, env('MEDIA_PREFIX')) - 1;

        $s3Domain = Str::substr($url, $start, $end);
        $url = Str::replace($s3Domain, env('AWS_STORAGE_URL', $s3Domain), $url);

        return $url;
    }
}
