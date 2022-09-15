<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    const Superadmin = 'Superadmin';
    const Admin = 'Admin';
    const Encoder = 'Encoder';
    const Viewer = 'Viewer';
}
