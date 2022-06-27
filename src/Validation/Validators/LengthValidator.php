<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-06-27 14:40:36 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class LengthValidator extends Validator
{
    protected $minLen = 0;

    protected $maxLen = 0;

    protected $message = ':label长度必须介于 :minLen 与 :maxLen 之间';

    /**
     * @param int|int[] $minLen
     */
    public function __construct(Field $field, $minLen, ?string $message = null)
    {
        if (is_array($minLen)) {
            $this->minLen = max(intval($minLen[0] ?? 0), 0);
            $this->maxLen = max(intval($minLen[1] ?? 0), 0);
        } else {
            $this->minLen = max(intval($minLen[0]), 0);
        }

        parent::__construct($field, $message);
    }

    protected function validate($value, array $data, callable $next)
    {
        $len = is_array($value) ? count($value) : $this->strLen((string) $value);
        if ($len < $this->minLen || ($this->maxLen > 0 && $len > $this->maxLen)) {
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
