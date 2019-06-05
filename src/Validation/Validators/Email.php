<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:23:16 +0800
 */
namespace Teddy\Validation\Validators;

class Email extends ValidatorBase
{
    protected $message = [
        'default' => ':label不是有效的邮箱格式',
    ];

    public function validate($value, array $data)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->throwMessage();
        }

        return $value;
    }
}
