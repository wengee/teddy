<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

class Length extends ValidatorRuleBase
{
    protected $minLen = 0;

    protected $maxLen;

    protected $message = ':label长度必须介于 :minLen 与 :maxLen 之间';

    public function __construct(int $minLen, $maxLen = null, ?string $message = null)
    {
        if (is_string($maxLen)) {
            $message = $maxLen;
            $maxLen = null;
        }

        $this->minLen = max($minLen, 0);
        $this->message = $message ?: $this->message;
        if (is_int($maxLen)) {
            $this->maxLen = max($maxLen, 1);
        }
    }

    protected function validate($value, array $data, callable $next)
    {
        $len = is_array($value) ? count($value) : strlen((string) $value);
        if ($len < $this->minLen || ($this->maxLen !== null && $len > $this->maxLen)) {
            $this->throwMessage([
                ':minLen' => $this->minLen,
                ':maxLen' => $this->maxLen ?: '无限',
            ]);
        }

        return $next($value, $data);
    }
}
