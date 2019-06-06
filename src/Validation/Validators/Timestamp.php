<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:11:30 +0800
 */
namespace Teddy\Validation\Validators;

class Timestamp extends ValidatorRuleBase
{
    protected $message = ':label不是有效的时间戳';

    protected function validate($value, array $data, callable $next)
    {
        if ($value === null) {
            return null;
        }

        $timestamp = $this->getTimestamp($value);
        if ($timestamp === false) {
            $this->throwMessage();
        }

        return $next($timestamp, $data);
    }

    protected function getTimestamp($t)
    {
        if ($t instanceof \DateTime) {
            return $t->getTimestamp();
        } elseif (is_int($t)) {
            return $t;
        } elseif (is_string($t)) {
            return strtotime($t);
        }

        return false;
    }
}
