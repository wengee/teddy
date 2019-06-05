<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 15:19:31 +0800
 */
namespace Teddy\Validation;

use Teddy\Validation\Validators\Callback;

class Validation
{
    const VALIDATORS = [
        'after'     => Validators\After::class,
        'alpha'     => Validators\Alpha::class,
        'alphaNum'  => Validators\AlphaNum::class,
        'before'    => Validators\Before::class,
        'boolean'   => Validators\Boolean::class,
        'callback'  => Validators\Callback::class,
        'datetime'  => Validators\DateTime::class,
        'digit'     => Validators\Digit::class,
        'float'     => Validators\Double::class,
        'email'     => Validators\Email::class,
        'eq'        => Validators\Equal::class,
        'notIn'     => Validators\ExclusionIn::class,
        'gt'        => Validators\GreatThan::class,
        'in'        => Validators\InclusionIn::class,
        'integer'   => Validators\Integer::class,
        'length'    => Validators\Length::class,
        'lt'        => Validators\LessThan::class,
        'mobile'    => Validators\Mobile::class,
        'number'    => Validators\Numericality::class,
        'regex'     => Validators\Regex::class,
        'required'  => Validators\Required::class,
        'string'    => Validators\Str::class,
        'timestamp' => Validators\Timestamp::class,
        'trim'      => Validators\Trim::class,
        'url'       => Validators\Url::class,

        'bool'      => Validators\Boolean::class,
        'date'      => Validators\DateTime::class,
        'int'       => Validators\Integer::class,
        'same'      => Validators\Equal::class,
        'str'       => Validators\Str::class,

    ];

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
        if (is_string($rule) && isset(self::VALIDATORS[$rule])) {
            $className = self::VALIDATORS[$rule];
            $rule = new $className(...$args);
        } elseif (is_callable($rule)) {
            $rule = new Callback($rule);
        }

        if ($rule instanceof ValidatorInterface) {
            if (empty($this->rules[$field])) {
                $this->rules[$field] = [];
            }

            $rule->setName($field);
            if ($label !== null) {
                $rule->setLabel($label);
            }

            $this->rules[$field][] = $rule;
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
        $rules = array_merge($this->rules, $rules);

        $ret = [];
        foreach ($rules as $key => $list) {
            if (!is_string($key)) {
                continue;
            }

            $value = array_get($data, $key);
            foreach ($list as $rule) {
                if (is_callable($rule)) {
                    $rule = new Callback($rule);
                }

                if ($rule instanceof ValidatorInterface) {
                    $value = $rule->validate($value, $data);
                }
            }

            array_set($ret, $key, $value);
        }

        return $ret;
    }
}
