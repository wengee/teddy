<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

class GreatThan extends ValidatorRuleBase
{
    protected $value;

    protected $message = ':label必须大于:value';

    public function __construct($value, ?string $message = null)
    {
        $this->value = $value;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        if (!$this->checkCondition($value)) {
            $this->throwMessage([
                ':value' => $this->value,
            ]);
        }

        return $next($value, $data);
    }

    protected function checkCondition($value)
    {
        return $value > $this->value;
    }
}
