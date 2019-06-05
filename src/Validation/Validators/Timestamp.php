<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:04:34 +0800
 */
namespace Teddy\Validation\Validators;

class Timestamp extends ValidatorBase
{
    protected $message = [
        'default' => ':label不是有效的时间戳',
    ];

    public function validate($value, array $data, callable $next)
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
