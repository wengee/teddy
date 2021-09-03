<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class DateTimeValidator extends Validator
{
    protected $format = 'Y-m-d H:i:s';

    protected $message = ':label不是合法的日期时间格式';

    public function __construct(Field $field, string $format = '', ?string $message = null)
    {
        $this->format = $format ?: $this->format;
        parent::__construct($field, $message);
    }

    protected function validate($value, array $data, callable $next)
    {
        if (null === $value) {
            return $next($value, $data);
        }

        $value = $this->formatTime($value);
        if (false === $value) {
            $this->throwError();
        }

        return $next($value, $data);
    }

    protected function formatTime($t)
    {
        if ($t instanceof \DateTime) {
            return $t->format($this->format);
        }

        if (is_int($t)) {
            return date($this->format, $t);
        }

        if (is_string($t)) {
            $timestamp = strtotime($t);
            if (false !== $timestamp) {
                return date($this->format, $timestamp);
            }
        }

        return false;
    }
}
