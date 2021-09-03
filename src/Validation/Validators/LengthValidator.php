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

class LengthValidator extends Validator
{
    protected $minLen = 0;

    protected $maxLen;

    protected $message = ':label长度必须介于 :minLen 与 :maxLen 之间';

    public function __construct(Field $field, int $minLen, $maxLen = null, ?string $message = null)
    {
        if (is_string($maxLen)) {
            $message = $maxLen;
            $maxLen  = null;
        }

        $this->minLen = max($minLen, 0);
        if (is_int($maxLen)) {
            $this->maxLen = max($maxLen, 1);
        }

        parent::__construct($field, $message);
    }

    protected function validate($value, array $data, callable $next)
    {
        $len = is_array($value) ? count($value) : $this->strLen((string) $value);
        if ($len < $this->minLen || (null !== $this->maxLen && $len > $this->maxLen)) {
            $this->throwError([
                ':minLen' => $this->minLen,
                ':maxLen' => $this->maxLen ?: '无限',
            ]);
        }

        return $next($value, $data);
    }

    protected function strLen(string $str): int
    {
        $ret = 0;
        if (function_exists('mb_strlen')) {
            $ret = mb_strlen($str);
        } else {
            $ret = strlen($str);
        }

        return $ret;
    }
}
