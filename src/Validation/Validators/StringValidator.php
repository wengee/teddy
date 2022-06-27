<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-06-27 17:55:10 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class StringValidator extends Validator
{
    protected $trim = true;

    protected $message = ':label必须为字符串';

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

    protected function validate($value, array $data, callable $next)
    {
        $value = strval($value);
        if ($this->trim) {
            $value = trim($value);
        }

        return $next($value, $data);
    }
}
