<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

class UrlValidator extends Validator
{
    protected $message = ':label不是一个合法的URL';

    public function validate($value, array $data, callable $next)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
