<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:37:08 +0800
 */
namespace Teddy\Validation\Validators;

class Str extends ValidatorBase
{
    protected $trim = false;

    protected $required = false;

    protected $minLen = 0;

    protected $maxLen = null;

    protected $pattern;

    protected $message = [
        'required'  => ':label不能为空',
        'min'       => ':label长度不能小于 :minLen',
        'max'       => ':label长度不能超过 :maxLen',
        'pattern'   => ':label不符合指定规则',
    ];

    public function __construct(array $options = [])
    {
        $this->hydrate($options);
    }

    public function validate($value, array $data, callable $next)
    {
        $value = strval($value);
        if ($this->trim) {
            $value = trim($value);
        }

        $strLen = strlen($value);
        if ($this->required && $strLen === 0) {
            $this->throwMessage('required');
        }

        if ($strLen < $this->minLen) {
            $this->throwMessage('min');
        }

        if ($this->maxLen !== null && $strLen > $maxLen) {
            $this->throwMessage('max');
        }

        if ($this->pattern && !preg_match($pattern, $value)) {
            $this->throwMessage('pattern');
        }

        return $next($value, $data);
    }

    protected function setTrim(bool $trim)
    {
        $this->trim = $trim;
    }

    protected function setRequired(bool $required)
    {
        $this->required = $required;
    }

    protected function setMinLen(int $value)
    {
        $this->minLen = $value;
    }

    protected function setMaxLen(int $value)
    {
        $this->maxLen = $value;
    }

    protected function setPattern(string $pattern)
    {
        $this->pattern = $pattern;
    }
}
