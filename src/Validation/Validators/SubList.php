<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:54:41 +0800
 */
namespace Teddy\Validation\Validators;

use Teddy\Validation\Validator;
use Teddy\Validation\ValidatorInterface;

class SubList extends ValidatorBase
{
    protected $subRules = [];

    public function __construct(array $rules)
    {
        foreach ($rules as $k => $v) {
            if (!is_string($k)) {
                continue;
            }

            if (strpos($k, '|')) {
                list($k, $label) = explode('|', $k);
            } else {
                $label = null;
            }

            if (!is_array($v)) {
                $v = [$v];
            }

            $this->subRules[$k] = new Validator($k);
            foreach ($v as $rule => $args) {
                if (is_int($rule)) {
                    $rule = $args;
                    $args = [];
                }

                $rule = Validator::make($rule, ...$args);
                if ($rule instanceof ValidatorInterface) {
                    $rule->setName($k);
                    if ($label) {
                        $rule->setLabel($label);
                    }

                    $this->subRules[$k]->add($rule);
                }
            }
        }
    }

    public function validate($value, array $data, callable $next)
    {
        $ret = [];
        $value = array_values((array) $value);
        foreach ($value as $item) {
            $ret[] = $this->validateItem((array) $item);
        }

        return $next($ret, $data);
    }

    protected function validateItem(array $data)
    {
        $filterd = [];
        foreach ($this->subRules as $validator) {
            $filterd = $validator->validate($data, $filterd);
        }

        return $filterd;
    }
}
