<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserDefinedFieldType extends Enum
{
    const Text = 1;
    const Number = 2;
    const Dropdown = 3;
    const Date = 4;
}
