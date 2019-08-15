<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Interfaces\ValidatorRuleInterface;
use Teddy\Validation\Exception;

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

    protected function throwMessage(array $data = []): void
    {
        $data[':label'] = $this->label ?: '';
        throw new Exception(strtr($this->message, $data));
    }

    abstract protected function validate($value, array $data, callable $next);
}
