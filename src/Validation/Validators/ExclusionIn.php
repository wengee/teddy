<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:00:50 +0800
 */

namespace Teddy\Validation\Validators;

class ExclusionIn extends ValidatorRuleBase
{
    protected $domain = [];

    protected $strict = true;

    protected $message = ':label不在有效范围内';

    public function __construct(array $domain, bool $strict = true, ?string $message = null)
    {
        $this->domain = $domain;
        $this->strict = $strict;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        if (in_array($value, $this->domain, $this->strict)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
