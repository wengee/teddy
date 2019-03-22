<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:52:28 +0800
 */
namespace Teddy\Validation\Validators;

class Confirmation extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        $field = array_get($options, 'field');
        $with = array_get($options, 'with', $field . '_confirmation');

        $input = array_get($options, 'input', []);
        $val1 = array_get($input, $field);
        $val2 = array_get($input, $with);

        if ($val1 !== $val2) {
            $this->error(
                'Field :label must be the same as :with',
                $options
            );
        }
    }
}
