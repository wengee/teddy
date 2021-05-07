<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 23:35:12 +0800
 */

namespace Teddy\Validation\Fields;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use RuntimeException;
use Teddy\Validation\Validation;
use Teddy\Validation\Validators\AfterValidator;
use Teddy\Validation\Validators\AlphaNumValidator;
use Teddy\Validation\Validators\AlphaValidator;
use Teddy\Validation\Validators\ArrayValidator;
use Teddy\Validation\Validators\BeforeValidator;
use Teddy\Validation\Validators\CallbackValidator;
use Teddy\Validation\Validators\DateTimeValidator;
use Teddy\Validation\Validators\DateValidator;
use Teddy\Validation\Validators\DigitValidator;
use Teddy\Validation\Validators\EmailValidator;
use Teddy\Validation\Validators\GteValidator;
use Teddy\Validation\Validators\GtValidator;
use Teddy\Validation\Validators\IdCardValidator;
use Teddy\Validation\Validators\InValidator;
use Teddy\Validation\Validators\LengthValidator;
use Teddy\Validation\Validators\ListValidator;
use Teddy\Validation\Validators\LteValidator;
use Teddy\Validation\Validators\LtValidator;
use Teddy\Validation\Validators\MobileValidator;
use Teddy\Validation\Validators\NotInValidator;
use Teddy\Validation\Validators\NumberValidator;
use Teddy\Validation\Validators\OptionalValidator;
use Teddy\Validation\Validators\RegexValidator;
use Teddy\Validation\Validators\RequiredValidator;
use Teddy\Validation\Validators\SameValidator;
use Teddy\Validation\Validators\TimestampValidator;
use Teddy\Validation\Validators\UrlValidator;
use Teddy\Validation\Validators\Validator;

/**
 * @method static AnyField     any(?string $label = null)
 * @method static ArrayField   array(?string $label = null)
 * @method static BooleanField bool(?string $label = null)
 * @method static BooleanField boolean(?string $label = null)
 * @method static FloatField   float(?string $label = null)
 * @method static FloatField   double(?string $label = null)
 * @method static IntegerField int(?string $label = null)
 * @method static IntegerField integer(?string $label = null)
 * @method static ListField    list(?string $label = null)
 * @method static StringField  string(?string $label = null)
 * @method static StringField  str(?string $label = null)
 * @method static TrimField    trim(?string $label = null)
 */
abstract class Field
{
    protected const FIELD_LIST = [
        'any'     => AnyField::class,
        'array'   => ArrayField::class,
        'bool'    => BooleanField::class,
        'boolean' => BooleanField::class,
        'float'   => FloatField::class,
        'double'  => FloatField::class,
        'int'     => IntegerField::class,
        'integer' => IntegerField::class,
        'list'    => ListField::class,
        'string'  => StringField::class,
        'str'     => StringField::class,
        'trim'    => TrimField::class,
    ];

    protected const VALIDATOR_LIST = [
        'after'     => AfterValidator::class,
        'alphaNum'  => AlphaNumValidator::class,
        'alpha'     => AlphaValidator::class,
        'array'     => ArrayValidator::class,
        'before'    => BeforeValidator::class,
        'callback'  => CallbackValidator::class,
        'datetime'  => DateTimeValidator::class,
        'date'      => DateValidator::class,
        'digit'     => DigitValidator::class,
        'email'     => EmailValidator::class,
        'gte'       => GteValidator::class,
        'gt'        => GtValidator::class,
        'idcard'    => IdCardValidator::class,
        'in'        => InValidator::class,
        'length'    => LengthValidator::class,
        'list'      => ListValidator::class,
        'lte'       => LteValidator::class,
        'lt'        => LtValidator::class,
        'mobile'    => MobileValidator::class,
        'notIn'     => NotInValidator::class,
        'number'    => NumberValidator::class,
        'optional'  => OptionalValidator::class,
        'regex'     => RegexValidator::class,
        'required'  => RequiredValidator::class,
        'same'      => SameValidator::class,
        'timestamp' => TimestampValidator::class,
        'url'       => UrlValidator::class,

        'eq'        => SameValidator::class,
    ];

    protected $label;

    protected $default;

    protected $prefix;

    protected $condition = [];

    protected $tip;

    protected $validators = [];

    public function __construct(?string $label = null)
    {
        $this->label = $label;
    }

    public function __invoke($value, array $data)
    {
        return $value;
    }

    public static function __callStatic(string $method, array $arguments)
    {
        return self::factory($method, ...$arguments);
    }

    public static function factory(string $name, ...$arguments)
    {
        $className = self::FIELD_LIST[$name] ?? null;
        if (!$className) {
            throw new RuntimeException('Field ['.$name.'] is invalid.');
        }

        return new $className(...$arguments);
    }

    /**
     * @return static|string
     */
    public function label(?string $label = null)
    {
        if (null === $label) {
            return $this->label ?: '';
        }

        $this->label = $label;

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return static
     */
    public function default($value): self
    {
        $this->default = $value;

        return $this;
    }

    public function filter($value)
    {
        if (null === $value) {
            return $this->default;
        }

        return $this->filterValue($value);
    }

    /**
     * @param mixed $condition
     *
     * @return static
     */
    public function if($condition, ...$arguments): self
    {
        $this->condition['type'] = 0;
        if ($condition instanceof Closure) {
            $this->condition['type'] = 2;
            $this->condition['func'] = $condition;
        } elseif (is_string($condition)) {
            if ('!' === $condition[0]) {
                $this->condition['not'] = true;

                $condition = substr($condition, 1);
            }

            $this->condition['field'] = $condition;

            $argLen = count($arguments);
            if (1 === $argLen) {
                $this->condition['type']     = 1;
                $this->condition['operator'] = '=';
                $this->condition['value']    = $arguments[0];
            } elseif (2 === $argLen) {
                $this->condition['type']     = 1;
                $this->condition['operator'] = $arguments[0];
                $this->condition['value']    = $arguments[1];
            }
        }

        return $this;
    }

    /**
     * Add a validator to the field.
     *
     * All supported validators:
     * * then('after', $value, ?string $message = null)
     * * then('alphaNum', ?string $message = null)
     * * then('alpha', ?string $message = null)
     * * then('array', Field[]|Validation $validation)
     * * then('before', $value, ?string $message = null)
     * * then('callback', callable $func)
     * * then('datetime', string $format = '', ?string $message = null)
     * * then('date',, string $format = '' ?string $message = null)
     * * then('digit', ?string $message = null)
     * * then('email', ?string $message = null)
     * * then('gte', $value, ?string $message = null)
     * * then('gt', $value, ?string $message = null)
     * * then('idcard', ?string $message = null)
     * * then('in', array $domain, ?string $message = null)
     * * then('length', int $minLen, $maxLen = null, ?string $message = null)
     * * then('list', callable|Field|Field[]|Validation $validation)
     * * then('lte', $value, ?string $message = null)
     * * then('lt', $value, ?string $message = null)
     * * then('mobile', ?string $message = null)
     * * then('notIn', array $domain, ?string $message = null)
     * * then('number', ?string $message = null)
     * * then('optional', ?string $message = null)
     * * then('regex' string $pattern, $replacement = null, ?string $message = null)
     * * then('required', ?string $message = null)
     * * then('same', string $otherField, ?string $message = null)
     * * then('timestamp', ?string $message = null)
     * * then('url', ?string $message = null)
     * * then('eq', string $otherField, ?string $message = null)
     *
     * @param callback|Field[]|string|Validation|Validator $validator
     *
     * @return static
     */
    public function then($validator, ...$arguments): self
    {
        if (is_string($validator) && isset(self::VALIDATOR_LIST[$validator])) {
            $className = self::VALIDATOR_LIST[$validator];
            $validator = new $className($this, ...$arguments);
        } elseif (is_array($validator) || ($validator instanceof Validation)) {
            $validator = new ArrayValidator($this, $validator);
        } elseif (is_callable($validator)) {
            $validator = new CallbackValidator($this, $validator);
        }

        if ($validator instanceof Validator) {
            $this->validators[] = $validator;
        }

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function validate($value, array $data = [], bool $safe = false)
    {
        if (is_null($this->tip)) {
            $this->seedHandlerStack();
        }

        if ($this->checkCondition($data)) {
            $start = $this->tip;

            try {
                $value = $start($value, $data);
            } catch (Exception $e) {
                if ($safe) {
                    $value = null;
                } else {
                    throw $e;
                }
            }

            return $value;
        }

        return null;
    }

    protected function checkCondition(array $data): bool
    {
        if (empty($this->condition)) {
            return true;
        }

        $ret = false;
        if (2 === $this->condition['type']) {
            $ret = (bool) call_user_func($this->condition['func'], $data);
        } else {
            $value = Arr::get($data, $this->condition['field']);
            if (1 === $this->condition['type']) {
                switch ($this->condition['operator']) {
                    case '>':
                        $ret = $value > $this->condition['value'];

                        break;

                    case '>=':
                        $ret = $value >= $this->condition['value'];

                        break;

                    case '<':
                        $ret = $value < $this->condition['value'];

                        break;

                    case '<=':
                        $ret = $value <= $this->condition['value'];

                        break;

                    case '!=':
                        $ret = $value !== $this->condition['value'];

                        break;

                    default:
                        $ret = $value === $this->condition['value'];
                }
            } else {
                $ret = isset($value);
            }
        }

        return empty($this->condition['not']) ? $ret : !$ret;
    }

    protected function seedHandlerStack(): void
    {
        if (!is_null($this->tip)) {
            throw new RuntimeException('HandlerStack can only be seeded once.');
        }

        $this->tip = $this;
        $reversed  = array_reverse($this->validators);
        foreach ($reversed as $callable) {
            $next      = $this->tip;
            $this->tip = function ($value, array $data) use ($callable, $next) {
                return call_user_func($callable, $value, $data, $next);
            };
        }
    }

    abstract protected function filterValue($value);
}
