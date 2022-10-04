<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserLevel extends Enum
{
    const Superadmin = 'superadmin';
    const Admin = 'admin';
    const Regular = 'regular';
}
