<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:28:12 +0800
 */
namespace Teddy\Validation\Validators;

class GreatThan extends ValidatorBase
{
    protected $includeEq = false;

    protected $value;

    protected $message = [
        'gt'    => ':label必须大于:value',
        'gte'   => ':label必须大于或等于:value',
    ];

    public function __construct($value, bool $includeEq = false)
    {
        $this->value = $value;
        $this->includeEq = $includeEq;
    }

    public function validate($value, array $data)
    {
        if ($value < $this->value || (!$this->includeEq && $value <= $this->value)) {
            $this->throwMessage($this->includeEq ? 'gte' : 'gt');
        }

        return $value;
    }
}
