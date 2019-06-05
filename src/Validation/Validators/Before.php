<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 17:56:16 +0800
 */
namespace Teddy\Validation\Validators;

class After extends Timestamp
{
    protected $value;

    protected $timestamp = 0;

    protected $message = [
        'default' => ':label日期不能在:value之后',
    ];

    public function __construct($value)
    {
        $this->value = $value;
        $this->timestamp = $this->getTimestamp($value);
    }

    public function validate($value, array $data, callable $next)
    {
        $timestamp = $this->getTimestamp($value);
        if ($timestamp === false || $timestamp > $this->timestamp) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
