<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:30:11 +0800
 */
namespace Teddy\Validation\Validators;

class NotCondition extends Condition
{
    protected function checkCondition($value, array $data)
    {
        return !parent::checkCondition($value, $data);
    }
}
