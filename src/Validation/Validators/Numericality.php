<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:45:12 +0800
 */
namespace Teddy\Validation\Validators;

class Numericality extends ValidatorBase
{
    protected $message = [
        'default' => ':label不是有效的数字格式',
    ];

    public function validate($value, array $data)
    {
        $value = trim($value);
        if (!preg_match("/^-?\d+\.?\d*$/", $value)) {
            $this->throwMessage();
        }

        return $value + 0;
    }
}
