<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 16:43:28 +0800
 */

namespace Teddy\Validation\Validators;

class LtValidator extends GtValidator
{
    protected $message = ':label必须小于或等于:value';

    protected function checkCondition($value)
    {
        return $value < $this->value;
    }
}
