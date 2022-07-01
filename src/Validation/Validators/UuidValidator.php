<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

class UuidValidator extends Validator
{
    public const REGEX = '#^\w{8}(\-\w{4}){3}\-\w{12}$#i';

    protected $message = ':label格式不正确';

    public function validate($value, array $data, callable $next)
    {
        $value = trim(strval($value));
        if (!preg_match(self::REGEX, $value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
