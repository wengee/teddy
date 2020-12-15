<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-12-15 17:20:02 +0800
 */

namespace Teddy\Validation;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use RuntimeException;
use Teddy\Facades\Filter;
use Teddy\Interfaces\ValidatorRuleInterface;
use Teddy\Validation\Validators\Callback;

class Validator
{
    public const DEFAULT_VALIDATORS = [
        'after'     => Validators\After::class,
        'alpha'     => Validators\Alpha::class,
        'alphaNum'  => Validators\AlphaNum::class,
        'before'    => Validators\Before::class,
        'callback'  => Validators\Callback::class,
        'datetime'  => Validators\DateTime::class,
        'digit'     => Validators\Digit::class,
        'email'     => Validators\Email::class,
        'notIn'     => Validators\ExclusionIn::class,
        'gt'        => Validators\GreatThan::class,
        'gte'       => Validators\GreatThanOrEqual::class,
        'idcard'    => Validators\IdCard::class,
        'in'        => Validators\InclusionIn::class,
        'length'    => Validators\Length::class,
        'lt'        => Validators\LessThan::class,
        'lte'       => Validators\LessThanOrEqual::class,
        'mobile'    => Validators\Mobile::class,
        'number'    => Validators\Number::class,
        'optional'  => Validators\Optional::class,
        'regex'     => Validators\Regex::class,
        'required'  => Validators\Required::class,
        'same'      => Validators\Same::class,
        'timestamp' => Validators\Timestamp::class,
        'url'       => Validators\Url::class,

        'list'  => Validators\DataList::class,
        'array' => Validators\DataArray::class,
    ];

    protected $field;

    protected $label;

    protected $default;

    protected $filter;

    protected $condition = [];

    protected $tip;

    protected $validators = [];

    public function __construct(string $field, ?string $label = null)
    {
        $this->field = $field ?: $this->field;
        $this->label = $label ?: ucfirst($field);
    }

    public function __invoke($value, array $data)
    {
        return $value;
    }

    public static function rule($rule, ...$args): ?ValidatorRuleInterface
    {
        if (is_string($rule) && isset(self::DEFAULT_VALIDATORS[$rule])) {
            $className = self::DEFAULT_VALIDATORS[$rule];

            return new $className(...$args);
        }
        if (is_callable($rule)) {
            return new Callback($rule);
        }

        return ($rule instanceof ValidatorRuleInterface) ? $rule : null;
    }

    public static function make(string $field, ?string $label = null): self
    {
        return new self($field, $label);
    }

    public function push(ValidatorRuleInterface $validator): self
    {
        $validator->setLabel($this->label);
        $validator->setField($this->field);
        $this->validators[] = $validator;

        return $this;
    }

    public function default($value): self
    {
        $this->default = $value;

        return $this;
    }

    public function filter(string $filter, ...$args): self
    {
        if (empty($args)) {
            $this->filter = $filter;
        } else {
            array_unshift($args, $filter);
            $this->filter = $args;
        }

        return $this;
    }

    public function when($condition, ...$args): self
    {
        return $this->if($condition, ...$args);
    }

    public function if($condition, ...$args): self
    {
        $this->condition['type'] = 0;
        if ($condition instanceof Closure) {
            $this->condition['type'] = 2;
            $this->condition['func'] = $condition;
        } elseif (is_string($condition)) {
            if ('!' === $condition[0]) {
                $this->condition['not'] = true;
                $condition              = substr($condition, 1);
            }

            $this->condition['field'] = $condition;
            if (count($args)) {
                $this->condition['type']  = 1;
                $this->condition['value'] = $args[0];
            }
        }

        return $this;
    }

    public function add($rule, ...$args): self
    {
        return $this->then($rule, ...$args);
    }

    public function then($rule, ...$args): self
    {
        $rule = self::rule($rule, ...$args);
        if ($rule instanceof ValidatorRuleInterface) {
            $this->push($rule);
        }

        return $this;
    }

    public function validate(array $data, array $filterd = [], bool $silent = false): array
    {
        if (is_null($this->tip)) {
            $this->seedHandlerStack();
        }

        $start = $this->tip;
        $value = Arr::get($data, $this->field, $this->default);
        if ($this->checkCondition($value, $data)) {
            try {
                $value = $this->filterValue($value);
                $value = $start($value, $data);
            } catch (Exception $e) {
                if ($silent) {
                    $value = null;
                } else {
                    throw $e;
                }
            }

            Arr::set($filterd, $this->field, $value);
        }

        return $filterd;
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

    protected function checkCondition($value, array $data): bool
    {
        if (empty($this->condition)) {
            return true;
        }

        $ret = false;
        switch ($this->condition['type']) {
            case 2:
                $ret = (bool) call_user_func($this->condition['func'], $data);

                break;
            case 1:
                $ret = Arr::get($data, $this->condition['field']) == $this->condition['value'];

                break;
            default:
                $ret = isset($data[$this->condition['field']]);
        }

        return empty($this->condition['not']) ? $ret : !$ret;
    }

    protected function filterValue($value)
    {
        if (empty($this->filter)) {
            return $value;
        }

        return Filter::sanitize($value, $this->filter);
    }
}
