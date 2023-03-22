<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:33:50 +0800
 */

namespace Teddy\Validation\Validators;

use Closure;
use Teddy\Validation\Field;

class RegexValidator extends Validator
{
    protected $pattern;

    protected $replacement;

    protected string $message = ':label不符合指定规则';

    /**
     * @param string|string[] $pattern
     */
    public function __construct(Field $field, $pattern, ?string $message = null)
    {
        if (is_array($pattern)) {
            if (count($pattern) >= 2) {
                $this->pattern     = $pattern[0] ?? null;
                $this->replacement = $pattern[1] ?? null;
            } else {
                $this->pattern     = key($pattern);
                $this->replacement = current($pattern);
            }
        } else {
            $this->pattern = strval($pattern);
        }

        parent::__construct($field, $message);
    }

    public function validate($value, array $data, callable $next)
    {
        $value = strval($value);
        if (!$this->pattern) {
            return $next($value, $data);
        }

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
