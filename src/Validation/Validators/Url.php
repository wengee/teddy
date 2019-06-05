<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:43:14 +0800
 */
namespace Teddy\Validation\Validators;

class Url extends ValidatorBase
{
    protected $message = [
        'default' => ':label不是一个合法的URL',
    ];

    public function validate($value, array $data)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->throwMessage();
        }

        return $value;
    }
}
