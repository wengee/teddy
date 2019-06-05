<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:30:06 +0800
 */
namespace Teddy\Validation\Validators;

class LessThan extends ValidatorBase
{
    protected $includeEq = false;

    protected $value;

    protected $message = [
        'lt'    => ':label必须小于:value',
        'lte'   => ':label必须小于或等于:value',
    ];

    public function __construct($value, bool $includeEq = false)
    {
        $this->value = $value;
        $this->includeEq = $includeEq;
    }

    public function validate($value, array $data)
    {
        if ($value > $this->value || (!$this->includeEq && $value >= $this->value)) {
            $this->throwMessage($this->includeEq ? 'lte' : 'lt');
        }

        return $value;
    }
}
