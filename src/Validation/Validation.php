<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:50:07 +0800
 */
namespace Teddy\Validation;

use Teddy\Traits\Singleton;
use Teddy\Validation\ValidatorRuleInterface;

class Validation
{
    use Singleton;

    protected $rules = [];

    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    public static function make(array $rules = [])
    {
        return new self($rules);
    }

    public static function check(array $data, array $rules = [])
    {
        return self::instance()->validate($data, $rules);
    }

    public function add(string $field, Validator $validator)
    {
        $this->rules[$field] = $validator;
        return $this;
    }

    public function append(string $field, $rule, ...$args)
    {
        if (!($rule instanceof ValidatorRuleInterface)) {
            $rule = Validator::rule($rule, ...$args);
        }

        if (isset($this->rules[$field]) && ($rule instanceof ValidatorRuleInterface)) {
            $this->rules[$field]->push($rule);
        }
        return $this;
    }

    public function validate(array $data, array $rules = [])
    {
        if (!empty($rules)) {
            $rules = array_filter($rules, function ($item) {
                return $item instanceof Validator;
            });

            $rules = array_merge($this->rules, $rules);
        } else {
            $rules = $this->rules;
        }

        $filtered = [];
        foreach ($rules as $validator) {
            $filtered = $validator->validate($data, $filtered);
        }

        return $filtered;
    }
}
