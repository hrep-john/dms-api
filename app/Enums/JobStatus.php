<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class JobStatus extends Enum
{
    const InProgress = 'IN_PROGRESS';
    const Succeeded = 'SUCCEEDED';
}
