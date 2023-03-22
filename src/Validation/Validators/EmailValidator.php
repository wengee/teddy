<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:30:31 +0800
 */

namespace Teddy\Validation\Validators;

class EmailValidator extends Validator
{
    protected string $message = ':label不是有效的邮箱格式';

    public function validate($value, array $data, callable $next)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
