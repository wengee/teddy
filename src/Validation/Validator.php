<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:50:57 +0800
 */
namespace Teddy\Validation;

use RuntimeException;
use Teddy\Validation\Validators\Callback;

class Validator
{
    const DEFAULT_VALIDATORS = [
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
        'list'      => Validators\SubList::class,
        'timestamp' => Validators\Timestamp::class,
        'trim'      => Validators\Trim::class,
        'url'       => Validators\Url::class,
        'if'        => Validators\Condition::class,
        'ifNot'     => Validators\NotCondition::class,

        'bool'      => Validators\Boolean::class,
        'date'      => Validators\DateTime::class,
        'int'       => Validators\Integer::class,
        'same'      => Validators\Equal::class,
        'str'       => Validators\Str::class,
    ];

    protected $field;

    protected $tip;

    protected $validators = [];

    public static function make($rule, ...$args)
    {
        if (is_string($rule) && isset(self::DEFAULT_VALIDATORS[$rule])) {
            $className = self::DEFAULT_VALIDATORS[$rule];
            return new $className(...$args);
        } elseif (is_callable($rule)) {
            return new Callback($rule);
        }

        return $rule;
    }

    public function __construct(string $field, array $rules = [])
    {
        $this->field = $field;
        foreach ($rules as $rule) {
            if ($rule instanceof ValidatorInterface) {
                $this->add($rule);
            }
        }
    }

    public function __invoke($value, array $data)
    {
        return $value;
    }

    public function add(ValidatorInterface $validator)
    {
        $this->validators[] = $validator;
        return $this;
    }

    public function validate(array $data, array $filterd = [])
    {
        if (is_null($this->tip)) {
            $this->seedHandlerStack();
        }

        $start = $this->tip;
        $value = array_get($data, $this->field);
        $value = $start($value, $data);
        if (!($value instanceof Skip)) {
            array_set($filterd, $this->field, $value);
        }

        return $filterd;
    }

    protected function seedHandlerStack()
    {
        if (!is_null($this->tip)) {
            throw new RuntimeException('HandlerStack can only be seeded once.');
        }

        $this->tip = $this;
        $reversed = array_reverse($this->validators);
        foreach ($reversed as $callable) {
            $next = $this->tip;
            $this->tip = function ($value, array $data) use ($callable, $next) {
                return call_user_func($callable, $value, $data, $next);
            };
        }
    }
}
