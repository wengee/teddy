<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-08 15:30:27 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;
use Teddy\Validation\ValidationError;

abstract class Validator
{
    /**
     * @var Field
     */
    protected $field;

    protected $message = ':label不符合指定规则';

    public function __construct(Field $field, ?string $message = null)
    {
        $this->field   = $field;
        $this->message = $message ?: $this->message;
    }

    public function __invoke($value, array $data, callable $next)
    {
        return $this->validate($value, $data, $next);
    }

    protected function throwError(array $data = []): void
    {
        $data[':label'] = $this->field->getLabel();

        throw new ValidationError(strtr($this->message, $data));
    }

    abstract protected function validate($value, array $data, callable $next);
}
