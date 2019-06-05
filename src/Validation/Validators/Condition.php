<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:58:51 +0800
 */
namespace Teddy\Validation\Validators;

use Teddy\Validation\Skip;

class Condition extends ValidatorBase
{
    protected $conditionType = 0;

    protected $conditionNot = false;

    protected $conditionField;

    protected $conditionFunc;

    protected $conditionValue;

    public function __construct($condition, ...$args)
    {
        if (is_callable($condition)) {
            $this->conditionType = 2;
            $this->conditionFunc = $condition;
        } elseif (is_string($condition)) {
            $this->conditionField = $condition;

            if (count($args)) {
                $this->conditionType = 1;
                $this->conditionValue = $args[0];
            }
        }
    }

    public function validate($value, array $data, callable $next)
    {
        if ($this->checkCondition($value, $data)) {
            return $next($value, $data);
        }

        return Skip::instance();
    }

    protected function checkCondition($value, array $data)
    {
        switch ($this->conditionType) {
            case 2:
                return (bool) value($this->conditionFunc);

            case 1:
                return array_get($data, $this->conditionField) == $this->conditionValue;

            default:
                return isset($data[$this->conditionField]);
        }
    }
}
