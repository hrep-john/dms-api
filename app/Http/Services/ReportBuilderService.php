<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\ReportBuilderServiceInterface;
use App\Models\ReportBuilder;
use Storage;
use Str;

class ReportBuilderService extends BaseService implements ReportBuilderServiceInterface
{
    /**
    * RoleService constructor.
    *
    * @param Role $model
    */
    public function __construct(ReportBuilder $model)
    {
        parent::__construct($model);
    }

    public function getTemplateBySlug(string $slug) 
    {
        return $this->model->where('slug', $slug)->first();
    }

    protected function formatAttributes($attributes): array
    {
        $attributes['slug'] = Str::slug($attributes['name']);
        $attributes['tenant_id'] = \App\Helpers\tenant();

        return $attributes;
    }

    public function uploadFiles($model, $file) 
    {
        $file = $model
            ->addMedia($file)
            ->toMediaCollection('printout-template');

        return Storage::disk('s3')->temporaryUrl(  
            $file->getPath(),
            now()->addMinutes(1),
            ['ResponseContentDisposition' => 'attachment']
        );
    }
}