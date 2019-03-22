<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:53:09 +0800
 */
namespace Teddy\Validation\Validators;

use DateTime;

class Date extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        $format = array_get($options, 'format', 'Y-m-d');
        if (!$this->checkDate($value, $format)) {
            $this->error(
                ':label 必须为日期格式":format"',
                $options
            );
        }
    }

    private function checkDate($value, string $format)
    {
        if ($value instanceof DateTime) {
            return true;
        } elseif (!is_string($value)) {
            return false;
        }

        $date = DateTime::createFromFormat($format, $value);
        return $date === false ? false : true;
    }
}
