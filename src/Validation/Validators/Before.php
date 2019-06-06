<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 17:12:36 +0800
 */
namespace Teddy\Validation\Validators;

class Before extends Timestamp
{
    protected $value;

    protected $timestamp = 0;

    protected $message = ':label日期不能在:value之后';

    public function __construct($value, ?string $message = null)
    {
        $this->value = $value;
        $this->timestamp = $this->getTimestamp($value);
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        $timestamp = $this->getTimestamp($value);
        if ($timestamp === false || $timestamp > $this->timestamp) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }
}
