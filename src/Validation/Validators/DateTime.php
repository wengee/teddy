<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:44:14 +0800
 */
namespace Teddy\Validation\Validators;

class DateTime extends ValidatorBase
{
    protected $format = 'Y-m-d H:i:s';

    protected $message = [
        'default' => ':label不是合法的日期时间格式',
    ];

    public function __construct(string $format = '')
    {
        $this->format = $format ?: $this->format;
    }

    public function validate($value, array $data)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value->format($this->format);
        } elseif (is_int($value)) {
            return date($this->format, $value);
        } elseif (is_string($value)) {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                $this->throwMessage();
            }

            return date($this->format, $timestamp);
        }

        $this->throwMessage();
    }
}
