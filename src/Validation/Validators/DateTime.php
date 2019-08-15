<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

class DateTime extends ValidatorRuleBase
{
    protected $format = 'Y-m-d H:i:s';

    protected $message = ':label不是合法的日期时间格式';

    public function __construct(string $format = '', ?string $message = null)
    {
        $this->format = $format ?: $this->format;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        if ($value === null) {
            return $next($value, $data);
        }

        $value = $this->formatTime($value);
        if ($value === false) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }

    protected function formatTime($t)
    {
        if ($t instanceof \DateTime) {
            return $t->format($this->format);
        } elseif (is_int($t)) {
            return date($this->format, $t);
        } elseif (is_string($t)) {
            $timestamp = strtotime($t);
            if ($timestamp !== false) {
                return date($this->format, $timestamp);
            }
        }

        return false;
    }
}
