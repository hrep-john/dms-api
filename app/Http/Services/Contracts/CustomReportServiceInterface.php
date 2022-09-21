<?php

namespace App\Http\Services\Contracts;

interface CustomReportServiceInterface extends BaseServiceInterface
{
    public function report(array $attributes);
}