<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
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
