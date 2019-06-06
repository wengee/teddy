<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:12:07 +0800
 */
namespace Teddy\Validation\Validators;

class GreatThanOrEqual extends GreatThan
{
    protected $message = ':label必须大于或等于:value';

    protected function checkCondition($value)
    {
        return $value >= $this->value;
    }
}
