<?php 

namespace App\Helpers;

use App\Models\Tenant;

if (!function_exists('tenantDomain')) {
    function tenantDomain()
    {
        return request()->header('domain');
    }
}

if (!function_exists('tenant')) {
    function tenant()
    {
        $domain = request()->header('domain');

        return !is_null($domain) 
            ? Tenant::where('domain', $domain)->withoutGlobalScopes()->first()->id
            : null; 
    }
}