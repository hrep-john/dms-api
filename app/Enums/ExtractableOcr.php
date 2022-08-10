<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ExtractableOcr extends Enum
{
    const Pdf = 'application/pdf';
    const Tiff = 'application/tiff';
    const Jpeg = 'image/jpeg';
    const Png = 'image/png';
}
