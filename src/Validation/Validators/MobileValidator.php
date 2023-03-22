<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:33:02 +0800
 */

namespace Teddy\Validation\Validators;

class MobileValidator extends Validator
{
    public const REGEX = '/^1[3|4|5|6|7|8|9][0-9]{9}$/';

    protected string $message = ':label不是一个合法的手机号码';

    public function validate($value, array $data, callable $next)
    {
        $value = trim(strval($value));
        if (!preg_match(self::REGEX, $value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
