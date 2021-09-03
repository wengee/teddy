<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

class GreatThanOrEqualValidator extends GreatThanValidator
{
    protected $message = ':label必须大于或等于:value';

    protected function checkCondition($value)
    {
        return $value >= $this->value;
    }
}
