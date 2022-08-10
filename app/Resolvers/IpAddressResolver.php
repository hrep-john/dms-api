<?php

namespace App\Resolvers;

use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class IpAddressResolver implements Resolver
{
    public static function resolve(Auditable $auditable): string
    {
        $xForwardedFor = Request::header('x-forwarded-for', '0.0.0.0');
        $splittedIp = explode(':', $xForwardedFor);

        return $splittedIp[0];
    }
}
