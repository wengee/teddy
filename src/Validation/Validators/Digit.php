<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:00:55 +0800
 */
namespace Teddy\Validation\Validators;

class Digit extends ValidatorBase
{
    protected $message = [
        'default' => ':label只能包含数字',
    ];

    public function validate($value, array $data, callable $next)
    {
        if (!is_int($value) && !ctype_digit($value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
