<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:01:45 +0800
 */
namespace Teddy\Validation\Validators;

class Equal extends ValidatorBase
{
    protected $otherField;

    protected $message = [
        'default' => ':label与确认字段不一致',
    ];

    public function __construct(string $otherField)
    {
        $this->otherField = $otherField;
    }

    public function validate($value, array $data, callable $next)
    {
        $val1 = array_get($data, $this->field);
        $val2 = array_get($data, $this->otherField);

        if ($val1 !== $val2) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
