<?php

namespace App\Http\Services\Contracts;

interface UserDefinedFieldServiceInterface extends BaseServiceInterface
{
    public function formatUdfValues($udfs): Array;
}