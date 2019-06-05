<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:02:46 +0800
 */
namespace Teddy\Validation\Validators;

class Length extends ValidatorBase
{
    protected $minLen = 0;

    protected $maxLen;

    protected $message = [
        'min' => ':label长度不能小于 :minLen',
        'max' => ':label长度不能超过 :maxLen',
    ];

    public function __construct(int $minLen, ?int $maxLen = null)
    {
        $this->minLen = $minLen;
        $this->maxLen = $maxLen;
    }

    public function validate($value, array $data, callable $next)
    {
        $len = is_array($value) ? count($value) : strlen((string) $value);
        if ($len < $this->minLen) {
            $this->throwMessage('min');
        }

        if ($this->maxLen !== null && $len > $this->maxLen) {
            $this->throwMessage('max');
        }

        return $next($value, $data);
    }
}
