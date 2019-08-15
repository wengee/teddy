<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation;

use Teddy\Interfaces\ValidatorRuleInterface;
use Teddy\Traits\Singleton;

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

    public function add(string $field, $validator = null)
    {
        if (is_string($validator) || $validator === null) {
            $validator = Validator::make($field, $validator);
        }

        if ($validator instanceof Validator) {
            $this->rules[$field] = $validator;
            return $validator;
        }

        return null;
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
        if (method_exists($this, 'beforeValidate')) {
            $this->beforeValidate($data);
        }

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

        if (method_exists($this, 'afterValidate')) {
            $this->afterValidate($filtered);
        }

        return $filtered;
    }
}
