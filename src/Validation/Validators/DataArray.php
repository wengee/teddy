<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Validator;

class DataArray extends ValidatorRuleBase
{
    protected $rules = [];

    public function __construct(array $rules)
    {
        foreach ($rules as $key => $value) {
            if (!is_string($key) || !($value instanceof Validator)) {
                continue;
            }

            $this->rules[$key] = $value;
        }
    }

    protected function validate($value, array $data, callable $next)
    {
        $value = (array) $value;

        $filtered = [];
        foreach ($this->rules as $validator) {
            $filtered = $validator->validate($value, $filtered);
        }

        return $next($filtered, $data);
    }
}
