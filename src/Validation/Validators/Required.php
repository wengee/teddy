<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:59:18 +0800
 */
namespace Teddy\Validation\Validators;

class Required extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        if ($value === null ||
            (is_string($value) && strlen($value) === 0) ||
            (is_array($value) && count($value) === 0)) {
            $this->error('Field :label is required', $options);
        }
    }
}
