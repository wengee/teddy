<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 17:56:04 +0800
 */
namespace Teddy\Validation\Validators;

class AlphaNum extends ValidatorBase
{
    protected $message = [
        'default' => ':label只能是字母和数字',
    ];

    public function validate($value, array $data, callable $next)
    {
        if (!ctype_alnum($value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
