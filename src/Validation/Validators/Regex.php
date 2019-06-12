<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-12 16:36:46 +0800
 */
namespace Teddy\Validation\Validators;

use Closure;

class Regex extends ValidatorRuleBase
{
    protected $pattern;

    protected $replacement = null;

    protected $message = ':label不符合指定规则';

    public function __construct(string $pattern, $replacement = null, ?string $message = null)
    {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        $value = strval($value);
        if (!preg_match($this->pattern, $value)) {
            $this->throwMessage();
        }

        if ($this->replacement) {
            if ($this->replacement instanceof Closure) {
                $value = preg_replace_callback($this->pattern, $this->replacement, $value);
            } elseif (is_string($this->replacement)) {
                $value = preg_replace($this->pattern, $this->replacement, $value);
            }
        }

        return $next($value, $data);
    }
}
