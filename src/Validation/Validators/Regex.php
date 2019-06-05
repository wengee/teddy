<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:41:37 +0800
 */
namespace Teddy\Validation\Validators;

class Regex extends ValidatorBase
{
    protected $pattern;

    protected $message = [
        'default'   => ':label不符合指定规则',
        'param'     => '参数(pattern)不能为空',
    ];

    public function __construct(string $pattern = '')
    {
        $this->pattern = $pattern;
    }

    public function validate($value, array $data)
    {
        if (!$this->pattern) {
            $this->throwMessage('param');
        }

        $value = strval($value);
        if (!preg_match($this->pattern, $value)) {
            $this->throwMessage();
        }

        return $value;
    }
}
