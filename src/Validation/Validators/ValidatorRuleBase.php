<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-15 16:50:29 +0800
 */
namespace Teddy\Validation\Validators;

use Teddy\Validation\Exception;
use Teddy\Validation\ValidatorRuleInterface;

abstract class ValidatorRuleBase implements ValidatorRuleInterface
{
    protected $label;

    protected $message = ':label不符合指定规则';

    public function __construct(?string $message = null)
    {
        $this->message = $message ?: $this->message;
    }

    public function __invoke($value, array $data, callable $next)
    {
        return $this->validate($value, $data, $next);
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    public function setLabel(?string $label)
    {
        $this->label = $label;
        return $this;
    }

    public function validateValue($value, array $data = [])
    {
        return $this->validate($value, $data, function ($value, array $data) {
            return $value;
        });
    }

    protected function throwMessage(array $data = [])
    {
        $data[':label'] = $this->label ?: '';
        throw new Exception(strtr($this->message, $data));
    }

    abstract protected function validate($value, array $data, callable $next);
}
