<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:34:18 +0800
 */

namespace Teddy\Validation\Validators;

class RequiredValidator extends Validator
{
    protected string $message = ':label不能为空';

    public function validate($value, array $data, callable $next)
    {
        if (null === $value
            || (is_string($value) && 0 === strlen($value))
            || (is_array($value) && 0 === count($value))) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
