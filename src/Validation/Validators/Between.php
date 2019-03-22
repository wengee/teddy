<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:48:45 +0800
 */
namespace Teddy\Validation\Validators;

class Between extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        $min = array_get($options, 'min');
        $max = array_get($options, 'max');
        if ($min === null && $max === null) {
            $this->error('Option "min" or "max" is required.');
        }

        $failed = ($min !== null && $value < intval($min)) ||
            ($max !== null && $value > intval($max));

        if ($failed) {
            $message = ($min !== null && $max !== null) ?
                'Field :label must be within the range of :min to :max' :
                ($min === null ? 'Field :label must be less than :max' :
                    'Field :label must be greater than :min');

            $this->error($message, $options);
        }
    }
}
