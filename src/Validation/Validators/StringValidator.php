<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:34:37 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class StringValidator extends Validator
{
    protected bool $trim = true;

    protected string $message = ':label必须为字符串';

    /**
     * @param bool|string $trim
     */
    public function __construct(Field $field, $trim = true, ?string $message = null)
    {
        if (is_string($trim)) {
            $message = $trim;
            $trim    = true;
        }

        $this->trim = $trim;
        parent::__construct($field, $message);
    }

    public function validate($value, array $data, callable $next)
    {
        $value = strval($value);
        if ($this->trim) {
            $value = trim($value);
        }

        return $next($value, $data);
    }
}
