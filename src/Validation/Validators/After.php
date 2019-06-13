<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-13 14:07:26 +0800
 */
namespace Teddy\Validation\Validators;

class After extends Timestamp
{
    protected $value;

    protected $message = ':label日期不能在:value之前';

    public function __construct($value, ?string $message = null)
    {
        $this->value = $value;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        $timestamp = $this->getTimestamp($value);
        $myTimestamp = $this->getTimestamp($this->value);
        if ($timestamp === false || $timestamp < $myTimestamp) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
