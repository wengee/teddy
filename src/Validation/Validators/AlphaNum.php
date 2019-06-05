<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:54:04 +0800
 */
namespace Teddy\Validation\Validators;

class AlphaNum extends ValidatorBase
{
    protected $message = [
        'default' => ':label只能是字母和数字',
    ];

    public function validate($value, array $data)
    {
        if (!ctype_alnum($value)) {
            $this->throwMessage();
        }

        return $value;
    }
}
