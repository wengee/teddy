<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class AfterValidator extends TimestampValidator
{
    protected $value;

    protected $message = ':label日期不能在:value之前';

    public function __construct(Field $field, $value, ?string $message = null)
    {
        $this->value = $value;
        parent::__construct($field, $message);
    }

    protected function validate($value, array $data, callable $next)
    {
        $timestamp   = $this->getTimestamp($value);
        $myTimestamp = $this->getTimestamp($this->value);
        if (false === $timestamp || $timestamp < $myTimestamp) {
            $this->throwError([
                ':value' => $this->value,
            ]);
        }

        return $next($value, $data);
    }
}
