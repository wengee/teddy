<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:32:14 +0800
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
