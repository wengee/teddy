<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-08 15:16:57 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class CallbackValidator extends Validator
{
    protected $func;

    public function __construct(Field $field, callable $func)
    {
        $this->func = $func;
        parent::__construct($field);
    }

    protected function validate($value, array $data, callable $next)
    {
        $value = call_user_func($this->func, $value, $data);

        return $next($value, $data);
    }
}
