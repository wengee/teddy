<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

class Same extends ValidatorRuleBase
{
    protected $otherField;

    protected $message = ':label与确认字段不一致';

    public function __construct(string $otherField, ?string $message = null)
    {
        $this->otherField = $otherField;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        $val1 = array_get($data, $this->field);
        $val2 = array_get($data, $this->otherField);

        if ($val1 !== $val2) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
