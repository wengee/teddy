<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 16:28:16 +0800
 */

namespace Teddy\Validation\Validators;

class UrlValidator extends Validator
{
    protected $message = ':label不是一个合法的URL';

    protected function validate($value, array $data, callable $next)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
