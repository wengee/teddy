<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:02:52 +0800
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

    public function validate($value, array $data, callable $next)
    {
        if ($value > $this->value || (!$this->includeEq && $value >= $this->value)) {
            $this->throwMessage($this->includeEq ? 'lte' : 'lt');
        }

        return $next($value, $data);
    }
}
