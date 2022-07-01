<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-01 16:15:26 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Interfaces\ValidatorInterface;
use Teddy\Validation\Field;
use Teddy\Validation\ValidationError;

abstract class Validator implements ValidatorInterface
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

    abstract public function validate($value, array $data, callable $next);

    protected function throwError(array $data = []): void
    {
        $data[':label'] = $this->field->getLabel();

        throw new ValidationError(strtr($this->message, $data));
    }
}
