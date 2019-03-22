<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:59:30 +0800
 */
namespace Teddy\Validation\Validators;

class Url extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->error('Field :label must be a url', $options);
        }
    }
}
