<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:50:31 +0800
 */
namespace Teddy\Validation\Validators;

use Exception;

class Callback extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        $func = array_pull($options, 'func');
        if (!$func || !is_callable($func)) {
            $this->error('Option "func" is required.', $options);
        }

        $args = array_pull($options, 'args', []);
        try {
            $success = $func($value, ...$args);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        if (!$success) {
            $this->error('Field :label is invalid', $options);
        }
    }
}
