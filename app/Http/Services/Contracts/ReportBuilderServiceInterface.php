<?php

namespace App\Http\Services\Contracts;

interface ReportBuilderServiceInterface extends BaseServiceInterface
{
    public function getTemplateBySlug(string $slug);
}