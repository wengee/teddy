<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:52:45 +0800
 */
namespace Teddy\Validation;

class Validation
{
    protected static $instances = [];

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
        $className = get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className;
        }

        return self::$instances[$className]->validate($data, $rules);
    }

    public function add(string $field, string $label, $rule, ...$args)
    {
        $rule = Validator::make($rule, ...$args);
        if ($rule instanceof ValidatorInterface) {
            if (empty($this->rules[$field])) {
                $this->rules[$field] = new Validator($field);
            }

            $rule->setName($field);
            if ($label !== null) {
                $rule->setLabel($label);
            }

            $this->rules[$field]->add($rule);
        } elseif (is_array($rule)) {
            foreach ($rule as $r) {
                if (is_array($r)) {
                    $this->add($field, $label, ...$r);
                } else {
                    $this->add($field, $label, $r);
                }
            }
        }

        return $this;
    }

    public function replace(string $field, $rule, ?string $label = null)
    {
        $this->rules[$field] = [];
        return $this->add($field, $rule, $label);
    }

    public function validate(array $data, array $rules = [])
    {
        if (!empty($rules)) {
            foreach ($rules as $key => $item) {
                if (!is_array($item)) {
                    $item = [$item];
                }

                $rules[$key] = new Validator($key, $item);
            }

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
