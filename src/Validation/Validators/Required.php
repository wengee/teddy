<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:03:36 +0800
 */
namespace Teddy\Validation\Validators;

class Required extends ValidatorBase
{
    protected $message = [
        'default' => ':label不能为空',
    ];

    public function validate($value, array $data, callable $next)
    {
        if ($value === null ||
            (is_string($value) && strlen($value) === 0) ||
            (is_array($value) && count($value) === 0)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
