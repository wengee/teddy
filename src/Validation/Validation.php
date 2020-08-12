<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 15:03:43 +0800
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
        $this->initialize();
    }

    public static function make(array $rules = []): self
    {
        return new self($rules);
    }

    public function add(string $field, $validator = null): ?Validator
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

    public function append(string $field, $rule, ...$args): self
    {
        if (!($rule instanceof ValidatorRuleInterface)) {
            $rule = Validator::rule($rule, ...$args);
        }

        if (isset($this->rules[$field]) && ($rule instanceof ValidatorRuleInterface)) {
            $this->rules[$field]->push($rule);
        }

        return $this;
    }

    public function validate(array $data, array $rules = [], bool $silent = false): array
    {
        $data = $this->beforeValidate($data);
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
            $filtered = $validator->validate($data, $filtered, $silent);
        }

        return $this->afterValidate($filtered);
    }

    public function check(array $data, array $rules = []): array
    {
        return $this->validate($data, $rules, true);
    }

    protected function initialize(): void
    {
    }

    protected function beforeValidate(array $data): array
    {
        return $data;
    }

    protected function afterValidate(array $data): array
    {
        return $data;
    }
}
