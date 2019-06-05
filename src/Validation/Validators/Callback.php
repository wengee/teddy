<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:56:40 +0800
 */
namespace Teddy\Validation\Validators;

class Callback extends ValidatorBase
{
    protected $func;

    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    public function validate($value, array $data)
    {
        return call_user_func($this->func, $value, $data);
    }
}
