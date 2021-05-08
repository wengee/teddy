<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-08 15:13:48 +0800
 */

namespace Teddy\Validation\Validators;

class LessThanValidator extends GreatThanValidator
{
    protected $message = ':label必须小于或等于:value';

    protected function checkCondition($value)
    {
        return $value < $this->value;
    }
}
