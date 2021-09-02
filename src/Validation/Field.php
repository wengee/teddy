<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 16:25:35 +0800
 */

namespace Teddy\Validation;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use RuntimeException;
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
use Teddy\Validation\Validators\GreatThanOrEqualValidator;
use Teddy\Validation\Validators\GreatThanValidator;
use Teddy\Validation\Validators\IdCardValidator;
use Teddy\Validation\Validators\InValidator;
use Teddy\Validation\Validators\IpValidator;
use Teddy\Validation\Validators\LengthValidator;
use Teddy\Validation\Validators\LessThanOrEqualValidator;
use Teddy\Validation\Validators\LessThanValidator;
use Teddy\Validation\Validators\ListValidator;
use Teddy\Validation\Validators\MobileValidator;
use Teddy\Validation\Validators\NotInValidator;
use Teddy\Validation\Validators\NumberValidator;
use Teddy\Validation\Validators\OptionalValidator;
use Teddy\Validation\Validators\RegexValidator;
use Teddy\Validation\Validators\RequiredValidator;
use Teddy\Validation\Validators\SameValidator;
use Teddy\Validation\Validators\TimestampValidator;
use Teddy\Validation\Validators\UrlValidator;
use Teddy\Validation\Validators\UuidValidator;
use Teddy\Validation\Validators\Validator;

class Field
{
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
        'gte'       => GreatThanOrEqualValidator::class,
        'gt'        => GreatThanValidator::class,
        'idcard'    => IdCardValidator::class,
        'in'        => InValidator::class,
        'ip'        => IpValidator::class,
        'length'    => LengthValidator::class,
        'list'      => ListValidator::class,
        'lte'       => LessThanOrEqualValidator::class,
        'lt'        => LessThanValidator::class,
        'mobile'    => MobileValidator::class,
        'notIn'     => NotInValidator::class,
        'number'    => NumberValidator::class,
        'optional'  => OptionalValidator::class,
        'regex'     => RegexValidator::class,
        'required'  => RequiredValidator::class,
        'same'      => SameValidator::class,
        'timestamp' => TimestampValidator::class,
        'url'       => UrlValidator::class,
        'uuid'      => UuidValidator::class,

        'eq'        => SameValidator::class,
    ];

    protected $name;

    protected $label;

    protected $default;

    protected $prefix;

    protected $filter;

    protected $condition = [];

    protected $tip;

    protected $validators = [];

    public function __construct(?string $label = null, ?string $name = null)
    {
        $this->label = $label;
        $this->name  = $name;
    }

    public function __invoke($value, array $data)
    {
        return $value;
    }

    /**
     * @return static
     */
    public static function make(?string $label = null, ?string $name = null): self
    {
        return new static($label, $name);
    }

    /**
     * @return static
     */
    public function setName(?string $name = null): self
    {
        $this->name = $name ?: $this->name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function hasLabel(): bool
    {
        return (bool) $this->label;
    }

    /**
     * @return static
     */
    public function setLabel(?string $label = null): self
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?: '';
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

    /**
     * @param array|string $filter
     *
     * @return static
     */
    public function filter($filter, string ...$args): self
    {
        if (empty($args)) {
            $this->filter = $filter;
        } else {
            array_unshift($args, $filter);
            $this->filter = $args;
        }

        return $this;
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
     * * then('ip', int $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6, ?string $message = null)
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
     * * then('uuid', ?string $message = null)
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
    public function validate(array $data = [], array $validated = [], bool $safe = false)
    {
        if (!$this->name) {
            return $validated;
        }

        if ($this->checkCondition($data)) {
            $value = Arr::get($data, $this->name, $this->default);

            try {
                $value = $this->validateValue($value, $data);
            } catch (Exception $e) {
                if ($safe) {
                    $value = null;
                } else {
                    throw $e;
                }
            }

            Arr::set($validated, $this->name, $value);
        }

        return $validated;
    }

    public function validateValue($value, array $data = [])
    {
        if (is_null($this->tip)) {
            $this->seedHandlerStack();
        }

        return call_user_func($this->tip, $value, $data);
    }

    public function filterValue($value)
    {
        if (!$this->filter) {
            return $value;
        }

        $value = (null === $value) ? $this->default : $value;

        return app('filter')->sanitize($value, $this->filter);
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
}
