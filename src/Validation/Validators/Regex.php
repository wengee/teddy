<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:11:38 +0800
 */
namespace Teddy\Validation\Validators;

class Regex extends ValidatorRuleBase
{
    protected $pattern;

    protected $message = ':label不符合指定规则';

    public function __construct(string $pattern, ?string $message = null)
    {
        $this->pattern = $pattern;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        $value = strval($value);
        if (!preg_match($this->pattern, $value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
