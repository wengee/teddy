<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:34:42 +0800
 */

namespace Teddy\Validation\Validators;

class TimestampValidator extends Validator
{
    protected string $message = ':label不是有效的时间戳';

    public function validate($value, array $data, callable $next)
    {
        if (null === $value) {
            return null;
        }

        $timestamp = $this->getTimestamp($value);
        if (false === $timestamp) {
            $this->throwError();
        }

        return $next($timestamp, $data);
    }

    protected function getTimestamp($t)
    {
        if ($t instanceof \DateTime) {
            return $t->getTimestamp();
        }

        if (is_int($t)) {
            return $t;
        }

        if (is_string($t)) {
            return strtotime($t);
        }

        return false;
    }
}
