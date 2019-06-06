<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:11:48 +0800
 */
namespace Teddy\Validation\Validators;

class LessThanOrEqual extends GreatThan
{
    protected $message = ':label必须小于或等于:value';

    protected function checkCondition($value)
    {
        return $value <= $this->value;
    }
}
