<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:00:44 +0800
 */
namespace Teddy\Validation\Validators;

class DateTime extends ValidatorBase
{
    protected $format = 'Y-m-d H:i:s';

    protected $message = [
        'default' => ':label不是合法的日期时间格式',
    ];

    public function __construct(string $format = '')
    {
        $this->format = $format ?: $this->format;
    }

    public function validate($value, array $data, callable $next)
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
