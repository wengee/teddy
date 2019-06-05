<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:53:23 +0800
 */
namespace Teddy\Validation\Validators;

class Alpha extends ValidatorBase
{
    protected $message = [
        'default' => ':label只能是字母',
    ];

    public function validate($value, array $data)
    {
        if (preg_match('/[^[:alpha:]]/imu', $value)) {
            $this->throwMessage();
        }

        return $value;
    }
}
