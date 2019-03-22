<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:58:35 +0800
 */
namespace Teddy\Validation\Validators;

class Numericality extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        if (!preg_match("/^-?\d+\.?\d*$/", $value)) {
            $this->error('Field :label does not have a valid numeric format', $options);
        }
    }
}
