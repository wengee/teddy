<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:01:11 +0800
 */

namespace Teddy\Validation\Validators;

class LessThan extends GreatThan
{
    protected $message = ':label必须小于或等于:value';

    protected function checkCondition($value)
    {
        return $value < $this->value;
    }
}
