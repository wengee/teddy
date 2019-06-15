<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-15 16:54:46 +0800
 */
namespace Teddy\Validation\Validators;

class Before extends Timestamp
{
    protected $value;

    protected $message = ':label日期不能在:value之后';

    public function __construct($value, ?string $message = null)
    {
        $this->value = $value;
        $this->message = $message ?: $this->message;
    }

    protected function validate($value, array $data, callable $next)
    {
        $timestamp = $this->getTimestamp($value);
        $myTimestamp = $this->getTimestamp($this->value);
        if ($timestamp === false || $timestamp > $myTimestamp) {
            $this->throwMessage([
                ':value' => $this->value,
            ]);
        }

        return $next($value, $data);
    }
}