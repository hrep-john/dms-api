<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\TenantSettingServiceInterface;
use App\Models\TenantSetting;
use File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Str;

class TenantSettingService extends BaseService implements TenantSettingServiceInterface
{
    /**
    * TenantSettingService constructor.
    *
    * @param TenantSetting $model
    */
    public function __construct(TenantSetting $model)
    {
        parent::__construct($model);
    }

    /**
    * @param $id
    * @return Model
    */
    public function findByDomain(string $domain): ?Collection
    {
        $test = $this->model->whereHas('tenant', function($query) use ($domain) {
            return $query->where('tenants.domain', $domain);
        });

        return $test->get();
    }

    /**
    * @param array $attributes
    *
    * @return Model
    */
    public function sync(Model $model, array $settings)
    {
        return $this->transaction(function() use ($model, $settings) {
            $data = [];

            foreach ($settings as $setting) {
                $data[] = array_merge($setting, [
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);
            }

            $result = $model->settings()->delete();
            $result = $model->settings()->insert($settings);

            return true;
        });
    }

    /**
    * @param File $file
    *
    * @return Model
    */
    public function upload($file)
    {
        return $this->transaction(function() use ($file) {
            $fileName = time() . '_' . $file->getClientOriginalName();

            try {
                $objectKey = Storage::disk('s3')->putFileAs('tenant-settings', $file, $fileName);
                $url = Storage::disk('s3')->url($objectKey);
            } catch (\Throwable $th) {
                throw $th;
            }

            $start = 0;
            $end = strpos($url, '/tenant-settings');

            $s3Domain = Str::substr($url, $start, $end);
            $url = Str::replace($s3Domain, env('AWS_STORAGE_URL', $s3Domain), $url);

            return $url;
        });
    }
}