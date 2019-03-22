<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:57:46 +0800
 */
namespace Teddy\Validation\Validators;

class Length extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        $min = (int) array_get($options, 'min', 0);
        $max = array_get($options, 'max');

        $len = is_array($value) ? count($value) : strlen((string) $value);
        if ($len < $min) {
            $this->error(
                'Field :label must be at least :min characters long',
                $options
            );
        } elseif ($max !== null && $len > $max) {
            $this->error(
                'Field :label must not exceed :max characters long',
                $options
            );
        }
    }
}
