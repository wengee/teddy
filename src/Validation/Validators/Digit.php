<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:22:32 +0800
 */
namespace Teddy\Validation\Validators;

class Digit extends ValidatorBase
{
    protected $message = [
        'default' => ':label只能包含数字',
    ];

    public function validate($value, array $data)
    {
        if (!is_int($value) && !ctype_digit($value)) {
            $this->throwMessage();
        }

        return $value;
    }
}
