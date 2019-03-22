<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:59:04 +0800
 */
namespace Teddy\Validation\Validators;

class Regex extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        $pattern = array_get($options, 'pattern');
        if (!$pattern) {
            $this->error('Option "pattern" is required.', $options);
        }

        if (preg_match($pattern, $value, $matches)) {
            $failed = $matches[0] != $value;
        } else {
            $failed = true;
        }

        if ($failed) {
            $this->error('Field :label does not match the required format', $options);
        }
    }
}
