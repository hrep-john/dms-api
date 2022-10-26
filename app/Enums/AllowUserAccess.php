<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class AllowUserAccess extends Enum
{
    const YesAllowAllUsers = 1;
    const YesAllowSelectedUsers = 2;
    const NoDontAllow = 3;
}
