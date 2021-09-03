<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

use Closure;
use Teddy\Validation\Field;

class RegexValidator extends Validator
{
    protected $pattern;

    protected $replacement;

    protected $message = ':label不符合指定规则';

    public function __construct(Field $field, string $pattern, $replacement = null, ?string $message = null)
    {
        $this->pattern     = $pattern;
        $this->replacement = $replacement;
        parent::__construct($field, $message);
    }

    protected function validate($value, array $data, callable $next)
    {
        $value = strval($value);
        if (!preg_match($this->pattern, $value)) {
            $this->throwError();
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
