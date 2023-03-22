<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:31:35 +0800
 */

namespace Teddy\Validation\Validators;

class LessThanValidator extends GreatThanValidator
{
    protected string $message = ':label必须小于或等于:value';

    protected function checkCondition($value)
    {
        return $value < $this->value;
    }
}
