<?php

namespace App\Models;

use App;
use App\Http\Services\Contracts\UserDefinedFieldServiceInterface;
use App\Http\Services\Contracts\UserServiceInterface;
use App\Traits\FilterDocumentsByTenant;
use Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends BaseModel implements HasMedia
{
    use HasFactory, Searchable, FilterDocumentsByTenant, InteractsWithMedia;

    protected $fillable = [
        'folder_id',
        'series_id',
        'file_name',
        'user_defined_field',
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
    ];

    public $asYouType = true;

    public function toSearchableArray() 
    {
        $document = $this
            ->where('id', '=', $this->id)
            ->first()
            ->toArray();

        return Arr::only($document, [
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
        ]);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
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
        $udfs = $this->flatten_udfs;
        $udfCollection = UserDefinedField::get(['key', 'settings']);

        foreach ($udfs as $key => $value) {
            if (!empty($value)) {
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
        return $this->folder->tenant_id ?? '';
    }

    private function getUdfCustomLabel($settings, $value) 
    {
        $label = '';

        if ($settings['source'] === 'custom') {
            $data = collect($settings['data'])->where('id', $value)->first();
            $label = $data['label'];
        } else if ($settings['source'] === 'users') {
            $label = App::make(UserServiceInterface::class)->find($value)->userInfo->full_name;
        }

        return $label;
    }

    public function getFlattenUdfsAttribute()
    {
        $udfs = App::make(UserDefinedFieldServiceInterface::class)->all(false);

        $initialData = [];

        $currentValue = JSON_DECODE($this->user_defined_field, true);

        foreach($udfs as $udf) {
            $initialData[$udf->key] = $currentValue[$udf->key] ?? null;
        }

        return $initialData;
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
