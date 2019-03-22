<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 18:05:14 +0800
 */
namespace Teddy\Validation\Validators;

use Teddy\Validation\Exception;
use Teddy\Validation\ValidatorInterface;

abstract class ValidatorBase implements ValidatorInterface
{
    protected function error(string $message, array $options = [])
    {
        $message = array_pull($options, 'message') ?: $message;

        $data = [];
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            if (is_string($value) || is_numeric($value)) {
                $data[':' . $key] = $value;
            }
        }

        $message = strtr($message, $data);
        throw new Exception($message);
    }

    abstract public function validate($value, array $options = []);
}
