<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Event extends Enum
{
    const Created = 'created';
    const Updated = 'updated';
    const Deleted = 'deleted';
    const Restored = 'restored';
}
