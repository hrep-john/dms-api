<?php

namespace App\Http\Services\Contracts;

use App\Models\ReportBuilder;

interface CustomReportServiceInterface extends BaseServiceInterface
{
    public function report(ReportBuilder $template, array $filters);
}