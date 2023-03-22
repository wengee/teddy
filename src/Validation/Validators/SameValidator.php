<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:34:27 +0800
 */

namespace Teddy\Validation\Validators;

use Illuminate\Support\Arr;
use Teddy\Validation\Field;

class SameValidator extends Validator
{
    protected string $otherField;

    protected string $message = ':label与确认字段不一致';

    public function __construct(Field $field, string $otherField, ?string $message = null)
    {
        $this->otherField = $otherField;
        parent::__construct($field, $message);
    }

    public function validate($value, array $data, callable $next)
    {
        $otherValue = Arr::get($data, $this->otherField);
        if ($value !== $otherValue) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
